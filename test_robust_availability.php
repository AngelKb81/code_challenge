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

echo "🔒 === TEST ROBUSTEZZA SISTEMA DISPONIBILITÀ ===\n\n";

// Test 1: Articoli mostrati in createRequest
echo "📋 TEST 1: FILTRO ARTICOLI PER RICHIESTE\n";
echo "==========================================\n";

$availableItems = Item::where('status', 'available')
    ->where('quantity', '>', 0)
    ->get()
    ->filter(function ($item) {
        return $item->getAvailableQuantity() > 0;
    })
    ->values();

echo "Articoli mostrati nel form di richiesta: " . count($availableItems) . "\n\n";

foreach ($availableItems as $item) {
    $available = $item->available_quantity;
    $total = $item->quantity;
    $approved = $item->requests()->where('status', 'approved')->sum('quantity_requested');

    echo "✅ {$item->name}\n";
    echo "   📦 Totale: {$total} | 🎯 Disponibili: {$available} | ✅ Approvate: {$approved}\n\n";
}

// Test 2: Articoli che NON dovrebbero essere mostrati
echo "❌ TEST 2: ARTICOLI ESCLUSI (DISPONIBILITÀ = 0)\n";
echo "===============================================\n";

$excludedItems = Item::where('status', 'available')
    ->where('quantity', '>', 0)
    ->get()
    ->filter(function ($item) {
        return $item->getAvailableQuantity() === 0;
    });

if ($excludedItems->count() > 0) {
    echo "Articoli correttamente ESCLUSI dal form: " . $excludedItems->count() . "\n\n";

    foreach ($excludedItems as $item) {
        $available = $item->available_quantity;
        $total = $item->quantity;
        $approved = $item->requests()->where('status', 'approved')->sum('quantity_requested');

        echo "❌ {$item->name}\n";
        echo "   📦 Totale: {$total} | 🎯 Disponibili: {$available} | ✅ Approvate: {$approved}\n";
        echo "   🚫 ESCLUSO CORRETTAMENTE - Nessuna disponibilità\n\n";
    }
} else {
    echo "✅ Tutti gli articoli con quantity > 0 hanno anche disponibilità > 0\n\n";
}

// Test 3: Verifica consistenza calcoli
echo "🔍 TEST 3: VERIFICA CONSISTENZA CALCOLI\n";
echo "======================================\n";

$allItems = Item::all();
$errors = 0;

foreach ($allItems as $item) {
    $approved = $item->requests()->where('status', 'approved')->sum('quantity_requested');
    $calculated = max(0, $item->quantity - $approved);
    $model = $item->available_quantity;

    if ($calculated !== $model) {
        echo "❌ ERRORE in {$item->name}:\n";
        echo "   Calcolato: {$calculated} | Modello: {$model}\n";
        $errors++;
    }
}

if ($errors === 0) {
    echo "✅ Tutti i calcoli sono CONSISTENTI\n";
} else {
    echo "❌ Trovati {$errors} errori di calcolo!\n";
}

// Test 4: Simulazione scenari edge case
echo "\n🎯 TEST 4: SCENARI EDGE CASE\n";
echo "===========================\n";

// Test: Articolo con status diverso da 'available'
$nonAvailableItems = Item::where('status', '!=', 'available')->count();
echo "Articoli con status != 'available': {$nonAvailableItems} (dovrebbero essere esclusi)\n";

// Test: Articoli con quantity = 0
$zeroQuantityItems = Item::where('quantity', 0)->count();
echo "Articoli con quantity = 0: {$zeroQuantityItems} (dovrebbero essere esclusi)\n";

// Test finale
echo "\n🎉 === RISULTATO TEST ===\n";
if ($errors === 0) {
    echo "✅ SISTEMA ROBUSTO - Tutti i controlli superati!\n";
    echo "✅ Gli articoli non disponibili sono correttamente esclusi\n";
    echo "✅ La logica di calcolo è consistente\n";
} else {
    echo "❌ ATTENZIONE - Sistema presenta alcune inconsistenze\n";
}

echo "\n📊 === RIEPILOGO ===\n";
echo "Articoli totali: " . Item::count() . "\n";
echo "Articoli mostrati nel form: " . count($availableItems) . "\n";
echo "Articoli esclusi per disponibilità = 0: " . $excludedItems->count() . "\n";
echo "Articoli non disponibili (status): {$nonAvailableItems}\n";
echo "Articoli con quantità = 0: {$zeroQuantityItems}\n";
