<?php

namespace App\Services;

use App\Models\Item;
use App\Models\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RequestApprovalService
{
    /**
     * Approve a request with concurrent request handling.
     * 
     * @param Request $request The request to approve
     * @param int $adminUserId The ID of the admin approving the request
     * @return array Result array with success status and message
     */
    public function approveRequest(Request $request, int $adminUserId): array
    {
        // Transaction per garantire atomicità
        return DB::transaction(function () use ($request, $adminUserId) {

            // 1. Verifica che la richiesta sia ancora pending
            if ($request->status !== 'pending') {
                return [
                    'success' => false,
                    'message' => 'Questa richiesta è già stata processata.',
                    'rejected_requests' => []
                ];
            }

            // 2. Gestione differenziata per tipo di richiesta
            if ($request->request_type === 'purchase_request') {
                return $this->approvePurchaseRequest($request, $adminUserId);
            } else {
                return $this->approveExistingItemRequest($request, $adminUserId);
            }
        });
    }

    /**
     * Approve a purchase request and create new item.
     */
    private function approvePurchaseRequest(Request $request, int $adminUserId): array
    {
        // 1. Crea il nuovo item nell'inventario
        $newItem = Item::create([
            'name' => $request->item_name,
            'description' => $request->item_description ?? 'Nuovo item da richiesta d\'acquisto',
            'category' => $request->item_category ?? 'Generale',
            'brand' => $request->item_brand ?? 'N/A',
            'quantity' => $request->quantity_requested,
            'location' => 'Da assegnare', // Valore di default
            'status' => 'available',
            'purchase_price' => $request->estimated_cost,
            'purchase_date' => now(),
            'supplier' => $request->supplier_info ?? 'N/A',
            'notes' => "Creato da richiesta di acquisto #{$request->id}. " . ($request->justification ?? $request->notes ?? ''),
        ]);

        // 2. Approva la richiesta e collega al nuovo item
        $request->update([
            'item_id' => $newItem->id,
            'status' => 'approved',
            'approved_by' => $adminUserId,
            'approved_at' => now(),
        ]);

        // 3. Log dell'operazione
        Log::info('Purchase request approved and item created', [
            'request_id' => $request->id,
            'new_item_id' => $newItem->id,
            'item_name' => $newItem->name,
            'quantity' => $newItem->quantity,
            'admin_id' => $adminUserId
        ]);

        return [
            'success' => true,
            'message' => "Richiesta di acquisto approvata! Nuovo articolo '{$newItem->name}' aggiunto all'inventario con {$newItem->quantity} unità.",
            'rejected_requests' => [],
            'created_item' => $newItem
        ];
    }

    /**
     * Approve an existing item request.
     */
    private function approveExistingItemRequest(Request $request, int $adminUserId): array
    {
        // Ricarica item con lock per prevenire race conditions
        $item = Item::where('id', $request->item_id)->lockForUpdate()->first();

        if (!$item) {
            return [
                'success' => false,
                'message' => 'Articolo non trovato.',
                'rejected_requests' => []
            ];
        }

        // Calcola quantità disponibile corrente
        $currentAvailable = $item->getAvailableQuantity();
        $requestedQuantity = $request->quantity_requested ?? 1;

        // Verifica disponibilità
        if ($currentAvailable < $requestedQuantity) {
            return [
                'success' => false,
                'message' => "Quantità insufficiente. Disponibili: {$currentAvailable}, Richieste: {$requestedQuantity}",
                'rejected_requests' => []
            ];
        }

        // Approva la richiesta corrente
        $request->update([
            'status' => 'approved',
            'approved_by' => $adminUserId,
            'approved_at' => now(),
        ]);

        // Calcola nuova quantità disponibile dopo l'approvazione
        $newAvailable = $currentAvailable - $requestedQuantity;

        // Gestisci richieste concorrenti se necessario
        $rejectedRequests = [];
        if ($newAvailable >= 0) {
            $rejectedRequests = $this->rejectExcessPendingRequests($item, $newAvailable);
        }

        // Aggiorna status dell'item
        $item->calculateAndUpdateStatus();

        // Log dell'operazione
        Log::info('Request approved', [
            'request_id' => $request->id,
            'item_id' => $item->id,
            'admin_id' => $adminUserId,
            'quantity_requested' => $requestedQuantity,
            'available_after' => $newAvailable,
            'rejected_requests' => count($rejectedRequests)
        ]);

        return [
            'success' => true,
            'message' => $this->buildSuccessMessage($request, $rejectedRequests),
            'rejected_requests' => $rejectedRequests
        ];
    }

    /**
     * Reject pending requests that exceed available quantity.
     */
    private function rejectExcessPendingRequests(Item $item, int $availableQuantity): array
    {
        // Trova tutte le richieste pending per questo item
        $pendingRequests = Request::where('item_id', $item->id)
            ->where('status', 'pending')
            ->orderBy('created_at', 'asc') // FIFO: prima arrivata, prima servita
            ->get();

        $rejectedRequests = [];
        $remainingQuantity = $availableQuantity;

        foreach ($pendingRequests as $pendingRequest) {
            $requestedQty = $pendingRequest->quantity_requested ?? 1;

            if ($remainingQuantity < $requestedQty) {
                // Non c'è abbastanza quantità per questa richiesta, rifiutala
                $pendingRequest->update([
                    'status' => 'rejected',
                    'approved_by' => Auth::id(),
                    'approved_at' => now(),
                    'rejection_reason' => 'Quantità non più disponibile a causa di altre richieste approvate.'
                ]);

                $rejectedRequests[] = [
                    'id' => $pendingRequest->id,
                    'user_name' => $pendingRequest->user->name ?? 'Unknown',
                    'quantity_requested' => $requestedQty
                ];
            } else {
                // C'è abbastanza quantità, sottrai dalla rimanente
                $remainingQuantity -= $requestedQty;
            }
        }

        return $rejectedRequests;
    }

    /**
     * Build success message with information about rejected requests.
     */
    private function buildSuccessMessage(Request $request, array $rejectedRequests): string
    {
        $message = 'Richiesta approvata con successo!';

        if (count($rejectedRequests) > 0) {
            $rejectedCount = count($rejectedRequests);
            $message .= " Attenzione: {$rejectedCount} richiesta/e in concorrenza sono state automaticamente rifiutate per mancanza di disponibilità.";
        }

        return $message;
    }

    /**
     * Get detailed availability info for an item.
     */
    public function getItemAvailabilityInfo(Item $item): array
    {
        $totalQuantity = $item->quantity;
        $approvedQuantity = $item->requests()
            ->where('status', 'approved')
            ->sum('quantity_requested');
        $pendingQuantity = $item->requests()
            ->where('status', 'pending')
            ->sum('quantity_requested');
        $availableQuantity = $totalQuantity - $approvedQuantity;

        return [
            'total_quantity' => $totalQuantity,
            'approved_quantity' => $approvedQuantity,
            'pending_quantity' => $pendingQuantity,
            'available_quantity' => $availableQuantity,
            'status' => $item->calculateStatus(),
            'can_fulfill_pending' => $availableQuantity >= $pendingQuantity
        ];
    }
}
