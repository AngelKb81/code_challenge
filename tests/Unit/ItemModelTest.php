<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Item;
use App\Models\Request;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class ItemModelTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Item $item;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['role' => 'user']);
        $this->item = Item::factory()->create([
            'name' => 'Test Item',
            'quantity' => 5,
            'status' => 'available'
        ]);
    }

    public function test_item_calculates_available_quantity_correctly()
    {
        // Nessuna richiesta - tutta la quantità disponibile
        $this->assertEquals(5, $this->item->getAvailableQuantity());

        // Aggiungi richiesta approvata
        Request::factory()->approved()->forItem($this->item)->quantity(2)->create();

        // Ricarica il modello per aggiornare le relazioni
        $this->item->refresh();

        $this->assertEquals(3, $this->item->getAvailableQuantity());

        // Aggiungi altra richiesta approvata
        Request::factory()->approved()->forItem($this->item)->quantity(1)->create();

        $this->item->refresh();

        $this->assertEquals(2, $this->item->getAvailableQuantity());
    }

    public function test_item_ignores_non_approved_requests_in_availability()
    {
        // Crea richieste con diversi stati
        Request::factory()->pending()->forItem($this->item)->quantity(2)->create();
        Request::factory()->rejected()->forItem($this->item)->quantity(1)->create();

        $this->item->refresh();

        // Solo le richieste approvate dovrebbero essere contate
        $this->assertEquals(5, $this->item->getAvailableQuantity());
    }

    public function test_item_status_calculation()
    {
        // Item con disponibilità completa
        $this->assertEquals('available', $this->item->calculateStatus());

        // Item con disponibilità parziale
        Request::factory()->approved()->forItem($this->item)->quantity(3)->create();
        $this->item->refresh();
        $this->assertEquals('partially_available', $this->item->calculateStatus());

        // Item senza disponibilità
        Request::factory()->approved()->forItem($this->item)->quantity(2)->create();
        $this->item->refresh();
        $this->assertEquals('unavailable', $this->item->calculateStatus());
    }

    public function test_item_is_available_method()
    {
        $this->assertTrue($this->item->isAvailable());

        // Occupa tutta la quantità
        Request::factory()->approved()->forItem($this->item)->quantity(5)->create();
        $this->item->refresh();

        $this->assertFalse($this->item->isAvailable());
    }

    public function test_item_relationships()
    {
        $request = Request::factory()->forItem($this->item)->create();

        $this->item->refresh();

        // Test relazione requests
        $this->assertTrue($this->item->requests->contains($request));

        // Test relazione activeRequests (approved + in_use)
        $request->update(['status' => 'approved']);
        $this->item->refresh();
        $this->assertTrue($this->item->activeRequests->contains($request));

        // Test relazione pendingRequests
        $request->update(['status' => 'pending']);
        $this->item->refresh();
        $this->assertTrue($this->item->pendingRequests->contains($request));
    }

    public function test_item_scope_by_category()
    {
        $electronicsItem = Item::factory()->category('Electronics')->create();
        $furnitureItem = Item::factory()->category('Furniture')->create();

        $electronicsItems = Item::byCategory('Electronics')->get();

        $this->assertTrue($electronicsItems->contains($electronicsItem));
        $this->assertFalse($electronicsItems->contains($furnitureItem));
    }

    public function test_item_scope_by_status()
    {
        $availableItem = Item::factory()->available()->create();
        $unavailableItem = Item::factory()->unavailable()->create();

        $availableItems = Item::byStatus('available')->get();

        $this->assertTrue($availableItems->contains($availableItem));
        $this->assertFalse($availableItems->contains($unavailableItem));
    }

    public function test_item_scope_available()
    {
        $availableItem = Item::factory()->available()->create();
        $unavailableItem = Item::factory()->unavailable()->create();

        $availableItems = Item::available()->get();

        $this->assertTrue($availableItems->contains($availableItem));
        $this->assertFalse($availableItems->contains($unavailableItem));
    }

    public function test_item_warranty_check()
    {
        $itemWithWarranty = Item::factory()->create([
            'warranty_expiry' => Carbon::now()->addYear()
        ]);

        $itemWithoutWarranty = Item::factory()->create([
            'warranty_expiry' => Carbon::now()->subYear()
        ]);

        $itemNullWarranty = Item::factory()->create([
            'warranty_expiry' => null
        ]);

        $this->assertTrue($itemWithWarranty->isUnderWarranty());
        $this->assertFalse($itemWithoutWarranty->isUnderWarranty());
        $this->assertFalse($itemNullWarranty->isUnderWarranty());
    }

    public function test_item_zero_quantity_is_never_available()
    {
        $zeroQuantityItem = Item::factory()->outOfStock()->create();

        $this->assertFalse($zeroQuantityItem->isAvailable());
        $this->assertEquals(0, $zeroQuantityItem->getAvailableQuantity());
        $this->assertEquals('available', $zeroQuantityItem->calculateStatus()); // Status should still be available but quantity 0
    }

    public function test_item_unavailable_status_overrides_quantity()
    {
        $unavailableItem = Item::factory()->create([
            'quantity' => 5,
            'status' => 'not_available'
        ]);

        // Anche con quantità disponibile, se lo status è not_available...
        $this->assertFalse($unavailableItem->isAvailable());
        $this->assertEquals('not_available', $unavailableItem->calculateStatus());
    }

    public function test_item_maintenance_status()
    {
        $maintenanceItem = Item::factory()->create([
            'status' => 'not_available'
        ]);

        $this->assertEquals('not_available', $maintenanceItem->status);
        $this->assertEquals('not_available', $maintenanceItem->calculateStatus());
    }

    public function test_available_quantity_attribute_is_appended()
    {
        $itemArray = $this->item->toArray();

        $this->assertArrayHasKey('available_quantity', $itemArray);
        $this->assertEquals($this->item->getAvailableQuantity(), $itemArray['available_quantity']);
    }

    public function test_item_serial_number_is_unique()
    {
        $item1 = Item::factory()->create(['serial_number' => 'TEST-123456']);

        $this->expectException(\Illuminate\Database\QueryException::class);
        Item::factory()->create(['serial_number' => 'TEST-123456']);
    }
}
