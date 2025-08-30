<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Item;
use App\Models\Request;
use App\Models\User;
use App\Services\ItemAvailabilityService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ItemAvailabilityServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ItemAvailabilityService $service;
    protected User $user;
    protected Item $item;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ItemAvailabilityService();
        
        // Crea utente di test
        $this->user = User::factory()->create(['role' => 'user']);
        
        // Crea item di test
        $this->item = Item::factory()->create([
            'name' => 'Test Item',
            'quantity' => 5,
            'status' => 'available'
        ]);
    }

    public function test_item_with_no_requests_is_fully_available()
    {
        $startDate = Carbon::today();
        $endDate = Carbon::today()->addDays(7);
        
        $isAvailable = $this->service->isAvailableForPeriod(
            $this->item, 
            $startDate, 
            $endDate, 
            3
        );
        
        $this->assertTrue($isAvailable);
    }

    public function test_item_with_conflicting_requests_is_not_available()
    {
        $startDate = Carbon::today();
        $endDate = Carbon::today()->addDays(7);
        
        // Crea richiesta che occupa tutta la quantità
        Request::factory()->create([
            'user_id' => $this->user->id,
            'item_id' => $this->item->id,
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'quantity_requested' => 5,
            'status' => 'approved'
        ]);
        
        $isAvailable = $this->service->isAvailableForPeriod(
            $this->item, 
            $startDate, 
            $endDate, 
            1
        );
        
        $this->assertFalse($isAvailable);
    }

    public function test_item_partially_available_during_period()
    {
        $startDate = Carbon::today();
        $endDate = Carbon::today()->addDays(7);
        
        // Crea richiesta che occupa parte della quantità
        Request::factory()->create([
            'user_id' => $this->user->id,
            'item_id' => $this->item->id,
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'quantity_requested' => 3,
            'status' => 'approved'
        ]);
        
        // Dovrebbe essere disponibile per 2 unità
        $isAvailable = $this->service->isAvailableForPeriod(
            $this->item, 
            $startDate, 
            $endDate, 
            2
        );
        
        $this->assertTrue($isAvailable);
        
        // Non dovrebbe essere disponibile per 3 unità
        $isAvailable = $this->service->isAvailableForPeriod(
            $this->item, 
            $startDate, 
            $endDate, 
            3
        );
        
        $this->assertFalse($isAvailable);
    }

    public function test_overlapping_periods_calculation()
    {
        $baseDate = Carbon::today();
        
        // Richiesta 1: giorni 1-3
        Request::factory()->create([
            'user_id' => $this->user->id,
            'item_id' => $this->item->id,
            'start_date' => $baseDate->format('Y-m-d'),
            'end_date' => $baseDate->copy()->addDays(2)->format('Y-m-d'),
            'quantity_requested' => 2,
            'status' => 'approved'
        ]);
        
        // Richiesta 2: giorni 2-4 (overlap)
        Request::factory()->create([
            'user_id' => $this->user->id,
            'item_id' => $this->item->id,
            'start_date' => $baseDate->copy()->addDay()->format('Y-m-d'),
            'end_date' => $baseDate->copy()->addDays(3)->format('Y-m-d'),
            'quantity_requested' => 1,
            'status' => 'approved'
        ]);
        
        // Test disponibilità nel periodo di overlap (giorno 2)
        $overlapStart = $baseDate->copy()->addDay();
        $overlapEnd = $baseDate->copy()->addDay();
        
        $isAvailable = $this->service->isAvailableForPeriod(
            $this->item, 
            $overlapStart, 
            $overlapEnd, 
            3 // 5 totali - 2 - 1 = 2 disponibili
        );
        
        $this->assertFalse($isAvailable);
        
        $isAvailable = $this->service->isAvailableForPeriod(
            $this->item, 
            $overlapStart, 
            $overlapEnd, 
            2
        );
        
        $this->assertTrue($isAvailable);
    }

    public function test_available_periods_calculation()
    {
        $startDate = Carbon::today();
        $endDate = Carbon::today()->addDays(10);
        
        // Crea richiesta che occupa giorni 3-5
        Request::factory()->create([
            'user_id' => $this->user->id,
            'item_id' => $this->item->id,
            'start_date' => $startDate->copy()->addDays(2)->format('Y-m-d'),
            'end_date' => $startDate->copy()->addDays(4)->format('Y-m-d'),
            'quantity_requested' => 5, // Occupa tutto
            'status' => 'approved'
        ]);
        
        $periods = $this->service->getAvailablePeriods($this->item, $startDate, $endDate);
        
        $this->assertIsArray($periods);
        $this->assertArrayHasKey('periods', $periods);
    }

    public function test_next_available_date_calculation()
    {
        $today = Carbon::today();
        
        // Occupa i prossimi 5 giorni
        Request::factory()->create([
            'user_id' => $this->user->id,
            'item_id' => $this->item->id,
            'start_date' => $today->format('Y-m-d'),
            'end_date' => $today->copy()->addDays(4)->format('Y-m-d'),
            'quantity_requested' => 5,
            'status' => 'approved'
        ]);
        
        $nextAvailable = $this->service->getNextAvailableDate($this->item);
        
        $this->assertInstanceOf(Carbon::class, $nextAvailable);
        $this->assertEquals($today->copy()->addDays(5)->format('Y-m-d'), $nextAvailable->format('Y-m-d'));
    }

    public function test_pending_requests_are_ignored()
    {
        $startDate = Carbon::today();
        $endDate = Carbon::today()->addDays(7);
        
        // Crea richiesta pending (non dovrebbe influenzare la disponibilità)
        Request::factory()->create([
            'user_id' => $this->user->id,
            'item_id' => $this->item->id,
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'quantity_requested' => 5,
            'status' => 'pending'
        ]);
        
        $isAvailable = $this->service->isAvailableForPeriod(
            $this->item, 
            $startDate, 
            $endDate, 
            5
        );
        
        $this->assertTrue($isAvailable, 'Pending requests should not affect availability');
    }

    public function test_rejected_requests_are_ignored()
    {
        $startDate = Carbon::today();
        $endDate = Carbon::today()->addDays(7);
        
        // Crea richiesta rifiutata (non dovrebbe influenzare la disponibilità)
        Request::factory()->create([
            'user_id' => $this->user->id,
            'item_id' => $this->item->id,
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'quantity_requested' => 5,
            'status' => 'rejected'
        ]);
        
        $isAvailable = $this->service->isAvailableForPeriod(
            $this->item, 
            $startDate, 
            $endDate, 
            5
        );
        
        $this->assertTrue($isAvailable, 'Rejected requests should not affect availability');
    }

    public function test_unavailable_items_are_never_available()
    {
        $this->item->status = 'unavailable';
        $this->item->save();
        
        $startDate = Carbon::today();
        $endDate = Carbon::today()->addDays(7);
        
        $isAvailable = $this->service->isAvailableForPeriod(
            $this->item, 
            $startDate, 
            $endDate, 
            1
        );
        
        $this->assertFalse($isAvailable, 'Unavailable items should never be available');
    }

    public function test_zero_quantity_items_are_never_available()
    {
        $this->item->quantity = 0;
        $this->item->save();
        
        $startDate = Carbon::today();
        $endDate = Carbon::today()->addDays(7);
        
        $isAvailable = $this->service->isAvailableForPeriod(
            $this->item, 
            $startDate, 
            $endDate, 
            1
        );
        
        $this->assertFalse($isAvailable, 'Zero quantity items should never be available');
    }
}
