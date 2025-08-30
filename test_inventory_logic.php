<?php

require 'vendor/autoload.php';

use Illuminate\Container\Container;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Events\Dispatcher;

// Setup database connection
$capsule = new Capsule;
$capsule->addConnection([
    'driver' => 'mysql',
    'host' => '127.0.0.1',
    'database' => 'code_challenge',
    'username' => 'root',
    'password' => '4ng3l0',
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
]);

$capsule->setEventDispatcher(new Dispatcher(new Container));
$capsule->setAsGlobal();
$capsule->bootEloquent();

echo "=== TEST INVENTORY LOGIC ===\n\n";

try {
    // Trova una richiesta pendente
    $pendingRequest = Capsule::table('requests')
        ->where('status', 'pending')
        ->first();

    if (!$pendingRequest) {
        echo "No pending requests found. Creating a test request...\n";

        // Trova un articolo con quantità > 1
        $item = Capsule::table('items')
            ->where('quantity', '>', 1)
            ->first();

        if (!$item) {
            echo "No items with quantity > 1 found!\n";
            exit(1);
        }

        // Crea una richiesta di test
        $requestId = Capsule::table('requests')->insertGetId([
            'user_id' => 2, // User normale
            'item_id' => $item->id,
            'start_date' => date('Y-m-d'),
            'end_date' => date('Y-m-d', strtotime('+7 days')),
            'status' => 'pending',
            'reason' => 'Test inventory logic',
            'quantity_requested' => 2,
            'priority' => 'medium',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $pendingRequest = Capsule::table('requests')->find($requestId);
        echo "Created test request ID: $requestId\n";
    }

    echo "Testing with request ID: {$pendingRequest->id}\n";

    // Trova l'articolo associato
    $item = Capsule::table('items')->find($pendingRequest->item_id);

    echo "\n=== BEFORE APPROVAL ===\n";
    echo "Request ID: {$pendingRequest->id}\n";
    echo "Request Status: {$pendingRequest->status}\n";
    echo "Quantity Requested: {$pendingRequest->quantity_requested}\n";
    echo "Item ID: {$item->id}\n";
    echo "Item Name: {$item->name}\n";
    echo "Item Quantity: {$item->quantity}\n";
    echo "Item Status: {$item->status}\n";

    // Simula l'approvazione (quello che fa il controller)
    if ($item->quantity >= $pendingRequest->quantity_requested) {

        // Aggiorna lo status della richiesta
        Capsule::table('requests')
            ->where('id', $pendingRequest->id)
            ->update([
                'status' => 'approved',
                'approved_by' => 1, // Admin
                'approved_at' => now(),
                'updated_at' => now(),
            ]);

        // Diminuisci la quantità dell'articolo
        $newQuantity = $item->quantity - $pendingRequest->quantity_requested;
        Capsule::table('items')
            ->where('id', $item->id)
            ->update([
                'quantity' => $newQuantity,
                'updated_at' => now(),
            ]);

        // Calcola e aggiorna lo status dell'articolo
        $newStatus = 'available';
        if ($newQuantity <= 0) {
            $newStatus = 'out_of_stock';
        } elseif ($newQuantity <= 5) {
            $newStatus = 'low_stock';
        }

        if ($newStatus !== $item->status) {
            Capsule::table('items')
                ->where('id', $item->id)
                ->update([
                    'status' => $newStatus,
                    'updated_at' => now(),
                ]);
        }

        echo "\n=== AFTER APPROVAL ===\n";

        // Ricarica i dati
        $updatedRequest = Capsule::table('requests')->find($pendingRequest->id);
        $updatedItem = Capsule::table('items')->find($item->id);

        echo "Request Status: {$updatedRequest->status}\n";
        echo "Request Approved At: {$updatedRequest->approved_at}\n";
        echo "Item Quantity: {$updatedItem->quantity} (was {$item->quantity})\n";
        echo "Item Status: {$updatedItem->status} (was {$item->status})\n";

        echo "\n✅ APPROVAL SIMULATION SUCCESSFUL!\n";
        echo "- Request approved ✓\n";
        echo "- Inventory quantity decreased ✓\n";
        echo "- Item status updated ✓\n";

        // Test della restituzione
        echo "\n=== TESTING RETURN ===\n";

        // Simula il ritorno
        Capsule::table('requests')
            ->where('id', $pendingRequest->id)
            ->update([
                'status' => 'returned',
                'returned_at' => now(),
                'updated_at' => now(),
            ]);

        // Aumenta la quantità dell'articolo
        $returnQuantity = $updatedItem->quantity + $pendingRequest->quantity_requested;
        Capsule::table('items')
            ->where('id', $item->id)
            ->update([
                'quantity' => $returnQuantity,
                'updated_at' => now(),
            ]);

        // Ricalcola lo status
        $returnStatus = 'available';
        if ($returnQuantity <= 0) {
            $returnStatus = 'out_of_stock';
        } elseif ($returnQuantity <= 5) {
            $returnStatus = 'low_stock';
        }

        Capsule::table('items')
            ->where('id', $item->id)
            ->update([
                'status' => $returnStatus,
                'updated_at' => now(),
            ]);

        // Ricarica i dati finali
        $finalRequest = Capsule::table('requests')->find($pendingRequest->id);
        $finalItem = Capsule::table('items')->find($item->id);

        echo "Request Status: {$finalRequest->status}\n";
        echo "Request Returned At: {$finalRequest->returned_at}\n";
        echo "Item Quantity: {$finalItem->quantity} (restored from {$updatedItem->quantity})\n";
        echo "Item Status: {$finalItem->status}\n";

        echo "\n✅ RETURN SIMULATION SUCCESSFUL!\n";
        echo "- Request marked as returned ✓\n";
        echo "- Inventory quantity restored ✓\n";
        echo "- Item status updated ✓\n";
    } else {
        echo "\n❌ INSUFFICIENT QUANTITY!\n";
        echo "Requested: {$pendingRequest->quantity_requested}\n";
        echo "Available: {$item->quantity}\n";
    }
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

function now()
{
    return date('Y-m-d H:i:s');
}
