<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Request;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class RequestModelTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $admin;
    protected Item $item;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create(['role' => 'user']);
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->item = Item::factory()->create([
            'name' => 'Test Item',
            'quantity' => 5,
            'status' => 'available'
        ]);
    }

    public function test_request_belongs_to_user()
    {
        $request = Request::factory()->forUser($this->user)->create();
        
        $this->assertInstanceOf(User::class, $request->user);
        $this->assertEquals($this->user->id, $request->user->id);
    }

    public function test_request_belongs_to_item_when_existing()
    {
        $request = Request::factory()->forItem($this->item)->create();
        
        $this->assertInstanceOf(Item::class, $request->item);
        $this->assertEquals($this->item->id, $request->item->id);
    }

    public function test_request_has_no_item_when_new_item_type()
    {
        $request = Request::factory()->newItem()->create();
        
        $this->assertNull($request->item);
        $this->assertNotNull($request->item_name);
        $this->assertNotNull($request->item_category);
        $this->assertEquals('purchase_request', $request->request_type);
    }

    public function test_request_scopes_work_correctly()
    {
        $pendingRequest = Request::factory()->pending()->create();
        $approvedRequest = Request::factory()->approved()->create();
        $rejectedRequest = Request::factory()->rejected()->create();
        
        // Test pending scope
        $pendingRequests = Request::pending()->get();
        $this->assertTrue($pendingRequests->contains($pendingRequest));
        $this->assertFalse($pendingRequests->contains($approvedRequest));
        
        // Test approved scope
        $approvedRequests = Request::approved()->get();
        $this->assertTrue($approvedRequests->contains($approvedRequest));
        $this->assertFalse($approvedRequests->contains($pendingRequest));
        
        // Test rejected scope
        $rejectedRequests = Request::rejected()->get();
        $this->assertTrue($rejectedRequests->contains($rejectedRequest));
        $this->assertFalse($rejectedRequests->contains($pendingRequest));
    }

    public function test_request_status_transitions()
    {
        $request = Request::factory()->pending()->create();
        
        $this->assertEquals('pending', $request->status);
        
        // Approve request
        $request->status = 'approved';
        $request->save();
        
        $this->assertEquals('approved', $request->status);
        
        // Return item
        $request->status = 'returned';
        $request->save();
        
        $this->assertEquals('returned', $request->status);
    }

    public function test_request_date_validation()
    {
        $request = Request::factory()->create([
            'start_date' => Carbon::today()->format('Y-m-d'),
            'end_date' => Carbon::today()->addDays(7)->format('Y-m-d')
        ]);
        
        $this->assertInstanceOf(Carbon::class, $request->start_date);
        $this->assertInstanceOf(Carbon::class, $request->end_date);
        $this->assertTrue($request->end_date->gt($request->start_date));
    }

    public function test_request_priority_levels()
    {
        $highPriorityRequest = Request::factory()->priority('high')->create();
        $mediumPriorityRequest = Request::factory()->priority('medium')->create();
        $lowPriorityRequest = Request::factory()->priority('low')->create();
        
        $this->assertEquals('high', $highPriorityRequest->priority);
        $this->assertEquals('medium', $mediumPriorityRequest->priority);
        $this->assertEquals('low', $lowPriorityRequest->priority);
    }

    public function test_request_quantity_requested()
    {
        $request = Request::factory()->quantity(3)->create();
        
        $this->assertEquals(3, $request->quantity_requested);
        $this->assertIsInt($request->quantity_requested);
    }

    public function test_request_for_specific_dates()
    {
        $startDate = '2024-01-01';
        $endDate = '2024-01-07';
        
        $request = Request::factory()->dates($startDate, $endDate)->create();
        
        $this->assertEquals($startDate, $request->start_date->format('Y-m-d'));
        $this->assertEquals($endDate, $request->end_date->format('Y-m-d'));
    }

    public function test_request_can_have_notes()
    {
        $notes = 'Special handling required for this request';
        $request = Request::factory()->create(['notes' => $notes]);
        
        $this->assertEquals($notes, $request->notes);
    }

    public function test_request_can_have_rejection_reason()
    {
        $rejectionReason = 'Item not available in requested period';
        $request = Request::factory()->rejected()->create([
            'rejection_reason' => $rejectionReason
        ]);
        
        $this->assertEquals('rejected', $request->status);
        $this->assertEquals($rejectionReason, $request->rejection_reason);
    }

    public function test_request_types_are_valid()
    {
        $existingItemRequest = Request::factory()->existingItem()->create();
        $newItemRequest = Request::factory()->newItem()->create();
        
        $this->assertEquals('existing_item', $existingItemRequest->request_type);
        $this->assertNotNull($existingItemRequest->item_id);
        
        $this->assertEquals('purchase_request', $newItemRequest->request_type);
        $this->assertNull($newItemRequest->item_id);
        $this->assertNotNull($newItemRequest->item_name);
    }

    public function test_request_timestamps_are_set()
    {
        $request = Request::factory()->create();
        
        $this->assertNotNull($request->created_at);
        $this->assertNotNull($request->updated_at);
        $this->assertInstanceOf(Carbon::class, $request->created_at);
        $this->assertInstanceOf(Carbon::class, $request->updated_at);
    }

    public function test_request_can_be_filtered_by_user()
    {
        $user1Requests = Request::factory()->count(3)->forUser($this->user)->create();
        $user2Requests = Request::factory()->count(2)->create();
        
        $userRequests = Request::where('user_id', $this->user->id)->get();
        
        $this->assertEquals(3, $userRequests->count());
        
        foreach ($user1Requests as $request) {
            $this->assertTrue($userRequests->contains($request));
        }
    }

    public function test_request_can_be_filtered_by_item()
    {
        $item1Requests = Request::factory()->count(2)->forItem($this->item)->create();
        $otherRequests = Request::factory()->count(3)->create();
        
        $itemRequests = Request::where('item_id', $this->item->id)->get();
        
        $this->assertEquals(2, $itemRequests->count());
        
        foreach ($item1Requests as $request) {
            $this->assertTrue($itemRequests->contains($request));
        }
    }

    public function test_request_factory_creates_realistic_data()
    {
        $request = Request::factory()->create();
        
        // Verifica che i dati generati siano realistici
        $this->assertNotEmpty($request->reason);
        $this->assertContains($request->status, ['pending', 'approved', 'rejected', 'in_use', 'returned', 'overdue']);
        $this->assertContains($request->priority, ['low', 'medium', 'high', 'urgent']);
        $this->assertGreaterThan(0, $request->quantity_requested);
        $this->assertContains($request->request_type, ['existing_item', 'purchase_request']);
    }
}
