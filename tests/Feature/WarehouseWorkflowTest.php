<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Request;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Carbon\Carbon;

class WarehouseWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Crea utenti di test
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->user = User::factory()->create(['role' => 'user']);
    }

    public function test_admin_can_create_item()
    {
        $this->actingAs($this->admin);

        $itemData = [
            'name' => 'Test Laptop',
            'category' => 'Electronics',
            'brand' => 'TestBrand',
            'quantity' => 5,
            'status' => 'available',
            'description' => 'Test laptop for unit testing',
            'location' => 'Warehouse A'
        ];

        $response = $this->post(route('warehouse.items.store'), $itemData);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('items', [
            'name' => 'Test Laptop',
            'category' => 'Electronics',
            'quantity' => 5
        ]);
    }

    public function test_user_cannot_create_item()
    {
        $this->actingAs($this->user);

        $itemData = [
            'name' => 'Test Laptop',
            'category' => 'Electronics',
            'quantity' => 5
        ];

        $response = $this->post(route('warehouse.items.store'), $itemData);
        $response->assertStatus(403);
    }

    public function test_user_can_view_available_items()
    {
        $this->actingAs($this->user);

        $availableItem = Item::factory()->create([
            'name' => 'Available Item',
            'status' => 'available',
            'quantity' => 3
        ]);

        $unavailableItem = Item::factory()->create([
            'name' => 'Unavailable Item',
            'status' => 'unavailable',
            'quantity' => 2
        ]);

        $response = $this->get(route('warehouse.items'));

        $response->assertInertia(
            fn(Assert $page) =>
            $page->component('Warehouse/Items')
                ->has('items.data')
        );
    }

    public function test_user_can_create_existing_item_request()
    {
        $this->actingAs($this->user);

        $item = Item::factory()->create([
            'name' => 'Test Item',
            'status' => 'available',
            'quantity' => 5
        ]);

        $requestData = [
            'request_type' => 'existing_item',
            'item_id' => $item->id,
            'start_date' => Carbon::now()->addDays(1)->format('Y-m-d'),
            'end_date' => Carbon::now()->addDays(7)->format('Y-m-d'),
            'quantity_requested' => 2,
            'reason' => 'Need for testing project',
            'priority' => 'medium'
        ];

        $response = $this->post(route('warehouse.requests.store'), $requestData);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('requests', [
            'user_id' => $this->user->id,
            'item_id' => $item->id,
            'quantity_requested' => 2,
            'status' => 'pending'
        ]);
    }

    public function test_user_can_create_new_item_request()
    {
        $this->actingAs($this->user);

        $requestData = [
            'request_type' => 'new_item',
            'item_name' => 'New Test Item',
            'item_category' => 'Electronics',
            'item_brand' => 'TestBrand',
            'start_date' => Carbon::now()->addDays(1)->format('Y-m-d'),
            'end_date' => Carbon::now()->addDays(7)->format('Y-m-d'),
            'quantity_requested' => 1,
            'reason' => 'Need new equipment for project',
            'priority' => 'high'
        ];

        $response = $this->post(route('warehouse.requests.store'), $requestData);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('requests', [
            'user_id' => $this->user->id,
            'item_name' => 'New Test Item',
            'item_category' => 'Electronics',
            'status' => 'pending'
        ]);
    }

    public function test_admin_can_approve_existing_item_request()
    {
        $this->actingAs($this->admin);

        $item = Item::factory()->create([
            'quantity' => 5,
            'status' => 'available'
        ]);

        $request = Request::factory()->create([
            'user_id' => $this->user->id,
            'item_id' => $item->id,
            'quantity_requested' => 2,
            'status' => 'pending',
            'request_type' => 'existing_item'
        ]);

        $response = $this->patch(route('warehouse.requests.approve', $request->id));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $request->refresh();
        $this->assertEquals('approved', $request->status);
        $this->assertEquals($this->admin->id, $request->admin_id);
    }

    public function test_admin_can_approve_new_item_request()
    {
        $this->actingAs($this->admin);

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

        $response = $this->patch(route('warehouse.requests.approve', $request->id));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $request->refresh();
        $this->assertEquals('approved', $request->status);
        $this->assertNotNull($request->item_id);

        // Verifica che l'item sia stato creato
        $this->assertDatabaseHas('items', [
            'name' => 'New Test Item',
            'category' => 'Electronics',
            'quantity' => 2
        ]);
    }

    public function test_admin_can_reject_request()
    {
        $this->actingAs($this->admin);

        $item = Item::factory()->create();
        $request = Request::factory()->create([
            'user_id' => $this->user->id,
            'item_id' => $item->id,
            'status' => 'pending'
        ]);

        $response = $this->patch(route('warehouse.requests.reject', $request->id), [
            'rejection_reason' => 'Not available for this period'
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $request->refresh();
        $this->assertEquals('rejected', $request->status);
        $this->assertEquals('Not available for this period', $request->rejection_reason);
        $this->assertEquals($this->admin->id, $request->admin_id);
    }

    public function test_user_cannot_approve_request()
    {
        $this->actingAs($this->user);

        $item = Item::factory()->create();
        $request = Request::factory()->create([
            'user_id' => $this->user->id,
            'item_id' => $item->id,
            'status' => 'pending'
        ]);

        $response = $this->patch(route('warehouse.requests.approve', $request->id));
        $response->assertStatus(403);
    }

    public function test_availability_validation_endpoint()
    {
        $this->actingAs($this->user);

        $item = Item::factory()->create([
            'quantity' => 5,
            'status' => 'available'
        ]);

        $validationData = [
            'item_id' => $item->id,
            'start_date' => Carbon::now()->addDays(1)->format('Y-m-d'),
            'end_date' => Carbon::now()->addDays(7)->format('Y-m-d'),
            'quantity_requested' => 3
        ];

        $response = $this->post(route('warehouse.validate-availability'), $validationData);

        $response->assertStatus(200);
        $response->assertJson([
            'available' => true,
            'item_name' => $item->name
        ]);
    }

    public function test_availability_validation_with_conflicts()
    {
        $this->actingAs($this->user);

        $item = Item::factory()->create([
            'quantity' => 2,
            'status' => 'available'
        ]);

        // Crea richiesta approvata che occupa tutto
        Request::factory()->create([
            'user_id' => $this->user->id,
            'item_id' => $item->id,
            'start_date' => Carbon::now()->addDays(1)->format('Y-m-d'),
            'end_date' => Carbon::now()->addDays(7)->format('Y-m-d'),
            'quantity_requested' => 2,
            'status' => 'approved'
        ]);

        $validationData = [
            'item_id' => $item->id,
            'start_date' => Carbon::now()->addDays(3)->format('Y-m-d'),
            'end_date' => Carbon::now()->addDays(5)->format('Y-m-d'),
            'quantity_requested' => 1
        ];

        $response = $this->post(route('warehouse.validate-availability'), $validationData);

        $response->assertStatus(200);
        $response->assertJson([
            'available' => false,
            'item_name' => $item->name
        ]);

        $responseData = $response->json();
        $this->assertArrayHasKey('conflicting_requests', $responseData);
    }

    public function test_user_can_mark_item_as_returned()
    {
        $this->actingAs($this->user);

        $item = Item::factory()->create();
        $request = Request::factory()->create([
            'user_id' => $this->user->id,
            'item_id' => $item->id,
            'status' => 'approved'
        ]);

        $response = $this->patch(route('warehouse.requests.return', $request->id));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $request->refresh();
        $this->assertEquals('returned', $request->status);
    }

    public function test_user_cannot_return_others_requests()
    {
        $this->actingAs($this->user);

        $otherUser = User::factory()->create(['role' => 'user']);
        $item = Item::factory()->create();
        $request = Request::factory()->create([
            'user_id' => $otherUser->id,
            'item_id' => $item->id,
            'status' => 'approved'
        ]);

        $response = $this->patch(route('warehouse.requests.return', $request->id));
        $response->assertStatus(403);
    }

    public function test_dashboard_statistics()
    {
        $this->actingAs($this->admin);

        // Crea test data
        Item::factory()->count(5)->create(['status' => 'available']);
        Item::factory()->count(2)->create(['status' => 'unavailable']);

        Request::factory()->count(3)->create(['status' => 'pending']);
        Request::factory()->count(5)->create(['status' => 'approved']);
        Request::factory()->count(1)->create(['status' => 'rejected']);

        $response = $this->get(route('warehouse.dashboard'));

        $response->assertInertia(
            fn(Assert $page) =>
            $page->component('Warehouse/Dashboard')
                ->has('stats')
                ->where('stats.total_items', 7)
                ->where('stats.available_items', 5)
                ->where('stats.pending_requests', 3)
                ->where('stats.approved_requests', 5)
        );
    }

    public function test_search_and_filter_items()
    {
        $this->actingAs($this->user);

        $electronics = Item::factory()->create([
            'name' => 'Laptop',
            'category' => 'Electronics',
            'brand' => 'Dell'
        ]);

        $furniture = Item::factory()->create([
            'name' => 'Office Chair',
            'category' => 'Furniture',
            'brand' => 'IKEA'
        ]);

        // Test search by name
        $response = $this->get(route('warehouse.items', ['search' => 'Laptop']));
        $response->assertInertia(
            fn(Assert $page) =>
            $page->has('items.data', 1)
        );

        // Test filter by category
        $response = $this->get(route('warehouse.items', ['category' => 'Electronics']));
        $response->assertInertia(
            fn(Assert $page) =>
            $page->has('items.data', 1)
        );
    }

    public function test_request_validation_rules()
    {
        $this->actingAs($this->user);

        // Test missing required fields
        $response = $this->post(route('warehouse.requests.store'), []);
        $response->assertSessionHasErrors(['request_type', 'start_date', 'end_date', 'reason']);

        // Test invalid date range
        $response = $this->post(route('warehouse.requests.store'), [
            'request_type' => 'existing_item',
            'start_date' => Carbon::now()->subDays(1)->format('Y-m-d'), // Past date
            'end_date' => Carbon::now()->addDays(7)->format('Y-m-d'),
            'reason' => 'Test'
        ]);
        $response->assertSessionHasErrors(['start_date']);

        // Test end_date before start_date
        $response = $this->post(route('warehouse.requests.store'), [
            'request_type' => 'existing_item',
            'start_date' => Carbon::now()->addDays(7)->format('Y-m-d'),
            'end_date' => Carbon::now()->addDays(1)->format('Y-m-d'), // Before start_date
            'reason' => 'Test'
        ]);
        $response->assertSessionHasErrors(['end_date']);
    }
}
