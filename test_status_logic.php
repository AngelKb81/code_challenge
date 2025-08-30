<?php

require 'vendor/autoload.php';

use App\Models\Item;
use App\Models\Request;

// Bootstrap Laravel
require_once 'bootstrap/app.php';

echo "=== TEST NUOVA LOGICA INVENTARIO ===\n\n";

try {
    // Trova il proiettore
    $proiettore = Item::where('name', 'LIKE', '%Proiettore%')->first();

    if (!$proiettore) {
        echo "Proiettore non trovato!\n";
        exit(1);
    }

    echo "=== PROIETTORE STATO ATTUALE ===\n";
    echo "ID: {$proiettore->id}\n";
    echo "Nome: {$proiettore->name}\n";
    echo "Quantità totale: {$proiettore->quantity}\n";
    echo "Status attuale: {$proiettore->status}\n";

    // Verifica richieste attive
    $activeRequests = $proiettore->activeRequests()->get();
    echo "Richieste attive: " . $activeRequests->count() . "\n";

    if ($activeRequests->count() > 0) {
        foreach ($activeRequests as $req) {
            echo "  - Richiesta {$req->id}: {$req->quantity_requested} unità, status: {$req->status}\n";
        }
    }

    // Calcola quantità disponibile con la nuova logica
    $availableQuantity = $proiettore->getAvailableQuantity();
    echo "Quantità disponibile (nuova logica): {$availableQuantity}\n";

    // Calcola il nuovo status
    $newStatus = $proiettore->calculateStatus();
    echo "Status calcolato (nuova logica): {$newStatus}\n";

    // Aggiorna lo status se necessario
    if ($proiettore->status !== $newStatus) {
        echo "\n=== AGGIORNAMENTO STATUS ===\n";
        echo "Status precedente: {$proiettore->status}\n";
        echo "Status nuovo: {$newStatus}\n";

        $proiettore->update(['status' => $newStatus]);
        echo "✅ Status aggiornato!\n";
    } else {
        echo "\n✅ Status già corretto!\n";
    }

    // Test con tutti gli articoli
    echo "\n=== CONTROLLO TUTTI GLI ARTICOLI ===\n";
    $items = Item::all();

    foreach ($items as $item) {
        $currentStatus = $item->status;
        $calculatedStatus = $item->calculateStatus();
        $availableQty = $item->getAvailableQuantity();

        if ($currentStatus !== $calculatedStatus) {
            echo "⚠️  {$item->name}:\n";
            echo "   Quantità totale: {$item->quantity}\n";
            echo "   Quantità disponibile: {$availableQty}\n";
            echo "   Status attuale: {$currentStatus}\n";
            echo "   Status calcolato: {$calculatedStatus}\n";
            echo "   🔄 Aggiornamento necessario\n\n";

            $item->update(['status' => $calculatedStatus]);
        } else {
            echo "✅ {$item->name}: Status corretto ({$currentStatus})\n";
        }
    }

    echo "\n=== VERIFICA FINALE PROIETTORE ===\n";
    $proiettore->refresh();
    echo "Status finale: {$proiettore->status}\n";
    echo "Quantità disponibile: {$proiettore->getAvailableQuantity()}\n";
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
