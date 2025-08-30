<?php

namespace App\Services;

use App\Models\Item;
use App\Models\Request;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ItemAvailabilityService
{
    /**
     * Calculate available periods for an item within a date range.
     * 
     * @param Item $item
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array
     */
    public function getAvailablePeriods(Item $item, Carbon $startDate, Carbon $endDate): array
    {
        // Se l'item ha disponibilità immediata, non serve calcolare i periodi
        if ($item->available_quantity > 0) {
            return [
                'immediate_available' => $item->available_quantity,
                'periods' => [
                    [
                        'start_date' => $startDate->format('Y-m-d'),
                        'end_date' => $endDate->format('Y-m-d'),
                        'available_quantity' => $item->available_quantity
                    ]
                ]
            ];
        }

        // Se non c'è disponibilità immediata, calcoliamo i periodi futuri
        return $this->calculateFuturePeriods($item, $startDate, $endDate);
    }

    /**
     * Calculate future availability periods based on return dates.
     * 
     * @param Item $item
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array
     */
    private function calculateFuturePeriods(Item $item, Carbon $startDate, Carbon $endDate): array
    {
        // Prende tutte le richieste approvate per questo item nel periodo esteso
        $approvedRequests = $item->requests()
            ->where('status', 'approved')
            ->where('request_type', 'existing_item')
            ->whereNotNull('start_date')
            ->whereNotNull('end_date')
            ->where(function ($query) use ($startDate, $endDate) {
                // Include richieste che si sovrappongono con il periodo di interesse
                $query->where(function ($q) use ($startDate, $endDate) {
                    $q->where('start_date', '<=', $endDate)
                        ->where('end_date', '>=', $startDate);
                });
            })
            ->get();

        // Crea una timeline giorno per giorno
        $timeline = [];
        $currentDate = $startDate->copy();

        while ($currentDate->lte($endDate)) {
            $dateKey = $currentDate->format('Y-m-d');
            $usedQuantity = 0;

            // Calcola quante unità sono occupate in questo giorno
            foreach ($approvedRequests as $request) {
                $reqStart = Carbon::parse($request->start_date);
                $reqEnd = Carbon::parse($request->end_date);

                // OPZIONE A: Item disponibile il giorno DOPO la data di fine
                // Se questo giorno è compreso nella richiesta (ESCLUDENDO il giorno dopo la fine)
                if ($currentDate->gte($reqStart) && $currentDate->lte($reqEnd)) {
                    $usedQuantity += $request->quantity_requested;
                }
            }

            $timeline[$dateKey] = max(0, $item->quantity - $usedQuantity);
            $currentDate->addDay();
        }

        // Raggruppa giorni consecutivi con la stessa disponibilità
        $availablePeriods = [];
        $currentPeriodStart = null;
        $currentPeriodEnd = null;
        $currentAvailability = null;

        foreach ($timeline as $date => $availability) {
            if ($availability > 0) { // Solo periodi con disponibilità > 0
                if ($currentAvailability === $availability && $currentPeriodEnd !== null) {
                    // Estende il periodo corrente
                    $currentPeriodEnd = $date;
                } else {
                    // Salva il periodo precedente se esisteva
                    if ($currentPeriodStart !== null && $currentAvailability > 0) {
                        $availablePeriods[] = [
                            'start_date' => $currentPeriodStart,
                            'end_date' => $currentPeriodEnd,
                            'available_quantity' => $currentAvailability
                        ];
                    }

                    // Inizia un nuovo periodo
                    $currentPeriodStart = $date;
                    $currentPeriodEnd = $date;
                    $currentAvailability = $availability;
                }
            } else {
                // Nessuna disponibilità, chiude il periodo corrente se esiste
                if ($currentPeriodStart !== null && $currentAvailability > 0) {
                    $availablePeriods[] = [
                        'start_date' => $currentPeriodStart,
                        'end_date' => $currentPeriodEnd,
                        'available_quantity' => $currentAvailability
                    ];
                    $currentPeriodStart = null;
                    $currentPeriodEnd = null;
                    $currentAvailability = null;
                }
            }
        }

        // Aggiungi l'ultimo periodo se esiste
        if ($currentPeriodStart !== null && $currentAvailability > 0) {
            $availablePeriods[] = [
                'start_date' => $currentPeriodStart,
                'end_date' => $currentPeriodEnd,
                'available_quantity' => $currentAvailability
            ];
        }

        return [
            'immediate_available' => 0,
            'periods' => $availablePeriods
        ];
    }

    /**
     * Calculate available quantity at a specific date.
     * 
     * @param Item $item
     * @param Carbon $date
     * @return int
     */
    private function calculateQuantityAtDate(Item $item, Carbon $date): int
    {
        // OPZIONE A: Item disponibile il giorno DOPO la data di fine
        $usedQuantity = $item->requests()
            ->where('status', 'approved')
            ->where('request_type', 'existing_item')
            ->where('start_date', '<=', $date)
            ->where('end_date', '>=', $date) // Include il giorno di fine nella prenotazione
            ->sum('quantity_requested');

        return max(0, $item->quantity - $usedQuantity);
    }

    /**
     * Get next available date for an item.
     * Returns today if item has immediate availability, otherwise the first future date with availability.
     * 
     * @param Item $item
     * @return Carbon|null
     */
    public function getNextAvailableDate(Item $item): ?Carbon
    {
        // Se l'item ha disponibilità immediata, restituisci oggi
        if ($item->available_quantity > 0) {
            return Carbon::today();
        }

        // Altrimenti, trova la prima data futura con disponibilità
        $startDate = Carbon::today();
        $endDate = Carbon::today()->addDays(90);
        $periods = $this->getAvailablePeriods($item, $startDate, $endDate);

        // Trova il primo periodo con disponibilità (escludendo oggi se ha 0 disponibilità)
        foreach ($periods['periods'] as $period) {
            $periodStart = Carbon::parse($period['start_date']);
            // Se il periodo inizia oggi ma non c'è disponibilità oggi, cerca il prossimo
            if ($periodStart->isToday() && $item->available_quantity == 0) {
                continue;
            }
            if ($periodStart->gte(Carbon::today()) && $period['available_quantity'] > 0) {
                return $periodStart;
            }
        }

        return null;
    }

    /**
     * Check if an item is available for a specific period.
     * 
     * @param Item $item
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param int $requestedQuantity
     * @return bool
     */
    public function isAvailableForPeriod(Item $item, Carbon $startDate, Carbon $endDate, int $requestedQuantity = 1): bool
    {
        // Calcola la disponibilità giorno per giorno per il periodo richiesto
        $currentDate = $startDate->copy();

        while ($currentDate->lte($endDate)) {
            $usedQuantity = $item->requests()
                ->where('status', 'approved')
                ->where('request_type', 'existing_item')
                ->where('start_date', '<=', $currentDate)
                ->where('end_date', '>=', $currentDate)
                ->sum('quantity_requested');

            $availableQuantity = max(0, $item->quantity - $usedQuantity);

            if ($availableQuantity < $requestedQuantity) {
                return false;
            }

            $currentDate->addDay();
        }

        return true;
    }
}
