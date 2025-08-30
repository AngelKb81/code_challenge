<?php

require_once 'vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;
use App\Models\Item;
use App\Models\Request as ItemRequest;

// Configurazione database
$capsule = new Capsule;
$capsule->addConnection([
    'driver' => 'mysql',
    'host' => '127.0.0.1',
    'database' => 'code_challenge',
    'username' => 'root',
    'password' => '4ng3l0',
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix' => '',
]);

$capsule->setAsGlobal();
$capsule->bootEloquent();

echo "=== TEST CALCOLO DISPONIBILITÀ ARTICOLI ===\n\n";

// Test iPad Pro M2 (id=10)
$item = Item::find(10);
if ($item) {
    echo "📱 ARTICOLO: {$item->name}\n";
    echo "   📦 Quantità totale: {$item->quantity}\n";

    $approvedRequests = $item->requests()->where('status', 'approved')->get();
    $totalApproved = $approvedRequests->sum('quantity_requested');

    echo "   ✅ Richieste approvate: {$totalApproved}\n";
    echo "   📋 Dettaglio richieste approvate:\n";

    foreach ($approvedRequests as $request) {
        echo "      - Request ID {$request->id}: qty {$request->quantity_requested} (User {$request->user_id})\n";
    }

    echo "   🎯 Quantità disponibile calcolata: {$item->getAvailableQuantity()}\n";
    echo "   🎯 Attributo available_quantity: {$item->available_quantity}\n";

    if ($item->getAvailableQuantity() === $item->available_quantity) {
        echo "   ✅ CALCOLO CORRETTO!\n";
    } else {
        echo "   ❌ ERRORE NEL CALCOLO!\n";
    }
} else {
    echo "❌ Articolo con ID 10 non trovato!\n";
}

echo "\n=== TEST SU ALTRI ARTICOLI ===\n";

// Test su tutti gli articoli con richieste approvate
$itemsWithApprovedRequests = Item::whereHas('requests', function ($query) {
    $query->where('status', 'approved');
})->take(5)->get();

foreach ($itemsWithApprovedRequests as $item) {
    $totalRequested = $item->requests()->where('status', 'approved')->sum('quantity_requested');
    $available = $item->getAvailableQuantity();
    $expectedAvailable = max(0, $item->quantity - $totalRequested);

    echo "📦 {$item->name}: ";
    echo "Tot:{$item->quantity} - Req:{$totalRequested} = Disp:{$available}";

    if ($available === $expectedAvailable) {
        echo " ✅\n";
    } else {
        echo " ❌ (Atteso: {$expectedAvailable})\n";
    }
}

echo "\n=== VERIFICA ATTRIBUTO APPEND ===\n";

$testItem = Item::find(10);
$itemArray = $testItem->toArray();

if (isset($itemArray['available_quantity'])) {
    echo "✅ Attributo 'available_quantity' presente nel JSON\n";
    echo "   Valore: {$itemArray['available_quantity']}\n";
} else {
    echo "❌ Attributo 'available_quantity' NON presente nel JSON\n";
    echo "   Attributi disponibili: " . implode(', ', array_keys($itemArray)) . "\n";
}

echo "\n=== TEST COMPLETATO ===\n";
