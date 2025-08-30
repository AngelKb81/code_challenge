<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Item;
use App\Models\Request;
use App\Models\User;
use App\Services\RequestApprovalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class RequestApprovalServiceTest extends TestCase
{
    use RefreshDatabase;

    protected RequestApprovalService $service;
    protected User $admin;
    protected User $user;
    protected Item $item;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new RequestApprovalService();

        // Crea admin e user di test
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->user = User::factory()->create(['role' => 'user']);

        // Crea item di test
        $this->item = Item::factory()->create([
            'name' => 'Test Item',
            'quantity' => 3,
            'status' => 'available'
        ]);
    }

    public function test_approve_existing_item_request_success()
    {
        $request = Request::factory()->create([
            'user_id' => $this->user->id,
            'item_id' => $this->item->id,
            'quantity_requested' => 2,
            'status' => 'pending',
            'request_type' => 'existing_item'
        ]);

        $result = $this->service->approveRequest($request, $this->admin->id);

        $this->assertTrue($result['success']);
        $this->assertStringContainsString('approvata', $result['message']);

        $request->refresh();
        $this->assertEquals('approved', $request->status);
        $this->assertEquals($this->admin->id, $request->admin_id);
    }

    public function test_approve_existing_item_request_insufficient_quantity()
    {
        // Richiesta che supera la quantità disponibile
        $request = Request::factory()->create([
            'user_id' => $this->user->id,
            'item_id' => $this->item->id,
            'quantity_requested' => 5, // Più di 3 disponibili
            'status' => 'pending',
            'request_type' => 'existing_item'
        ]);

        $result = $this->service->approveRequest($request, $this->admin->id);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Quantità richiesta non disponibile', $result['message']);

        $request->refresh();
        $this->assertEquals('pending', $request->status);
    }

    public function test_approve_new_item_request_success()
    {
        $request = Request::factory()->create([
            'user_id' => $this->user->id,
            'item_id' => null,
            'item_name' => 'New Test Item',
            'item_category' => 'Electronics',
            'item_brand' => 'TestBrand',
            'quantity_requested' => 2,
            'status' => 'pending',
            'request_type' => 'new_item'
        ]);

        $result = $this->service->approveRequest($request, $this->admin->id);

        $this->assertTrue($result['success']);
        $this->assertStringContainsString('approvata', $result['message']);
        $this->assertArrayHasKey('created_item', $result);

        // Verifica che il nuovo item sia stato creato
        $newItem = $result['created_item'];
        $this->assertInstanceOf(Item::class, $newItem);
        $this->assertEquals('New Test Item', $newItem->name);
        $this->assertEquals('Electronics', $newItem->category);
        $this->assertEquals(2, $newItem->quantity);

        $request->refresh();
        $this->assertEquals('approved', $request->status);
        $this->assertEquals($newItem->id, $request->item_id);
    }

    public function test_concurrent_requests_handling()
    {
        // Crea 3 richieste per lo stesso item (quantità totale > disponibile)
        $request1 = Request::factory()->create([
            'user_id' => $this->user->id,
            'item_id' => $this->item->id,
            'quantity_requested' => 2,
            'status' => 'pending',
            'request_type' => 'existing_item'
        ]);

        $request2 = Request::factory()->create([
            'user_id' => $this->user->id,
            'item_id' => $this->item->id,
            'quantity_requested' => 1,
            'status' => 'pending',
            'request_type' => 'existing_item'
        ]);

        $request3 = Request::factory()->create([
            'user_id' => $this->user->id,
            'item_id' => $this->item->id,
            'quantity_requested' => 1,
            'status' => 'pending',
            'request_type' => 'existing_item'
        ]);

        // Approva la prima richiesta (occupa 2 unità su 3)
        $result1 = $this->service->approveRequest($request1, $this->admin->id);
        $this->assertTrue($result1['success']);

        // Approva la seconda richiesta (occupa l'ultima unità)
        $result2 = $this->service->approveRequest($request2, $this->admin->id);
        $this->assertTrue($result2['success']);

        // La terza richiesta dovrebbe fallire (nessuna unità disponibile)
        $result3 = $this->service->approveRequest($request3, $this->admin->id);
        $this->assertFalse($result3['success']);

        // Verifica gli stati
        $request1->refresh();
        $request2->refresh();
        $request3->refresh();

        $this->assertEquals('approved', $request1->status);
        $this->assertEquals('approved', $request2->status);
        $this->assertEquals('pending', $request3->status);
    }

    public function test_automatic_rejection_of_excess_requests()
    {
        // Crea item con quantità 2
        $smallItem = Item::factory()->create([
            'quantity' => 2,
            'status' => 'available'
        ]);

        // Crea 3 richieste pending
        $requests = collect([
            Request::factory()->create([
                'user_id' => $this->user->id,
                'item_id' => $smallItem->id,
                'quantity_requested' => 1,
                'status' => 'pending',
                'request_type' => 'existing_item'
            ]),
            Request::factory()->create([
                'user_id' => $this->user->id,
                'item_id' => $smallItem->id,
                'quantity_requested' => 1,
                'status' => 'pending',
                'request_type' => 'existing_item'
            ]),
            Request::factory()->create([
                'user_id' => $this->user->id,
                'item_id' => $smallItem->id,
                'quantity_requested' => 1,
                'status' => 'pending',
                'request_type' => 'existing_item'
            ])
        ]);

        // Approva la prima richiesta
        $result = $this->service->approveRequest($requests[0], $this->admin->id);

        $this->assertTrue($result['success']);
        $this->assertGreaterThan(0, count($result['rejected_requests']));

        // Verifica che alcune richieste siano state automaticamente rifiutate
        foreach ($requests as $index => $request) {
            $request->refresh();
            if ($index === 0) {
                $this->assertEquals('approved', $request->status);
            } else {
                // Le altre potrebbero essere rejected automaticamente
                $this->assertContains($request->status, ['pending', 'rejected']);
            }
        }
    }

    public function test_get_item_availability_info()
    {
        // Crea alcune richieste per testare i calcoli
        Request::factory()->create([
            'user_id' => $this->user->id,
            'item_id' => $this->item->id,
            'quantity_requested' => 2,
            'status' => 'approved',
            'request_type' => 'existing_item'
        ]);

        Request::factory()->create([
            'user_id' => $this->user->id,
            'item_id' => $this->item->id,
            'quantity_requested' => 1,
            'status' => 'pending',
            'request_type' => 'existing_item'
        ]);

        $info = $this->service->getItemAvailabilityInfo($this->item);

        $this->assertArrayHasKey('total_quantity', $info);
        $this->assertArrayHasKey('approved_quantity', $info);
        $this->assertArrayHasKey('pending_quantity', $info);
        $this->assertArrayHasKey('available_quantity', $info);
        $this->assertArrayHasKey('status', $info);

        $this->assertEquals(3, $info['total_quantity']);
        $this->assertEquals(2, $info['approved_quantity']);
        $this->assertEquals(1, $info['pending_quantity']);
        $this->assertEquals(1, $info['available_quantity']);
    }

    public function test_reject_request_for_nonexistent_item()
    {
        $request = Request::factory()->create([
            'user_id' => $this->user->id,
            'item_id' => 99999, // Item inesistente
            'quantity_requested' => 1,
            'status' => 'pending',
            'request_type' => 'existing_item'
        ]);

        $result = $this->service->approveRequest($request, $this->admin->id);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('non trovato', $result['message']);
    }

    public function test_sku_generation_for_new_items()
    {
        $request = Request::factory()->create([
            'user_id' => $this->user->id,
            'item_id' => null,
            'item_name' => 'Test Electronics Item',
            'item_category' => 'Electronics',
            'quantity_requested' => 1,
            'status' => 'pending',
            'request_type' => 'new_item'
        ]);

        $result = $this->service->approveRequest($request, $this->admin->id);

        $this->assertTrue($result['success']);

        $newItem = $result['created_item'];
        $this->assertNotEmpty($newItem->serial_number);
        $this->assertStringStartsWith('ELE-', $newItem->serial_number);
    }

    public function test_fifo_order_for_pending_requests()
    {
        // Crea item con quantità limitata
        $limitedItem = Item::factory()->create([
            'quantity' => 1,
            'status' => 'available'
        ]);

        // Crea richieste con timestamp diversi
        $firstRequest = Request::factory()->create([
            'user_id' => $this->user->id,
            'item_id' => $limitedItem->id,
            'quantity_requested' => 1,
            'status' => 'pending',
            'request_type' => 'existing_item'
        ]);
        $firstRequest->created_at = Carbon::now()->subMinutes(10);
        $firstRequest->save();

        $secondRequest = Request::factory()->create([
            'user_id' => $this->user->id,
            'item_id' => $limitedItem->id,
            'quantity_requested' => 1,
            'status' => 'pending',
            'request_type' => 'existing_item'
        ]);
        $secondRequest->created_at = Carbon::now()->subMinutes(5);
        $secondRequest->save();

        // Approva la seconda richiesta (dovrebbe essere accettata perché c'è quantità)
        $result = $this->service->approveRequest($secondRequest, $this->admin->id);
        $this->assertTrue($result['success']);

        // La prima richiesta dovrebbe essere automaticamente rifiutata
        $this->assertGreaterThan(0, count($result['rejected_requests']));

        $firstRequest->refresh();
        $this->assertEquals('rejected', $firstRequest->status);
    }
}
