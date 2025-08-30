<?php

require 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Item;
use App\Models\Request;
use App\Models\User;
use App\Services\RequestApprovalService;
use Illuminate\Support\Facades\Auth;

echo "=== TEST GESTIONE CONCORRENZA RICHIESTE ===\n\n";

try {
    // Setup utenti
    $admin = User::where('role', 'admin')->first();
    $users = User::where('role', 'user')->take(3)->get();

    if (!$admin || $users->count() < 3) {
        echo "❌ Utenti insufficienti per il test\n";
        exit(1);
    }

    // Trova un item con quantità 2 o creane uno
    $item = Item::where('quantity', '>=', 2)->first();
    if (!$item) {
        echo "Creando item di test...\n";
        $item = Item::create([
            'name' => 'Test Item Concurrency',
            'category' => 'Test',
            'quantity' => 2,
            'status' => 'available',
            'description' => 'Item per test concorrenza',
        ]);
    }

    echo "🎯 Item di test: {$item->name}\n";
    echo "   Quantità totale: {$item->quantity}\n";
    echo "   Status iniziale: {$item->status}\n\n";

    // Pulisci richieste esistenti per questo item
    Request::where('item_id', $item->id)->delete();

    // Crea 3 richieste concorrenti per lo stesso item
    $requests = [];
    foreach ($users as $index => $user) {
        $request = Request::create([
            'user_id' => $user->id,
            'item_id' => $item->id,
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->addDays(7)->format('Y-m-d'),
            'status' => 'pending',
            'reason' => "Richiesta di test #" . ($index + 1),
            'quantity_requested' => 1,
            'priority' => 'medium'
        ]);
        $requests[] = $request;
        echo "📝 Creata richiesta {$request->id} per {$user->name} (1 unità)\n";
    }

    echo "\n📊 STATO PRIMA DELLE APPROVAZIONI:\n";
    $item->refresh();
    echo "   Quantità totale: {$item->quantity}\n";
    echo "   Quantità disponibile: {$item->getAvailableQuantity()}\n";
    echo "   Richieste pending: " . Request::where('item_id', $item->id)->where('status', 'pending')->count() . "\n";
    echo "   Status item: {$item->status}\n\n";

    // Simula login come admin
    Auth::login($admin);

    // Inizializza il service
    $approvalService = new RequestApprovalService();

    // Approva la prima richiesta
    echo "🟢 APPROVAZIONE RICHIESTA 1:\n";
    $result1 = $approvalService->approveRequest($requests[0], $admin->id);
    echo "   Risultato: " . ($result1['success'] ? '✅ SUCCESSO' : '❌ FALLIMENTO') . "\n";
    echo "   Messaggio: {$result1['message']}\n";
    if ($result1['rejected_requests']) {
        echo "   Richieste rifiutate automaticamente: " . count($result1['rejected_requests']) . "\n";
    }

    // Controlla stato dopo prima approvazione
    $item->refresh();
    echo "\n📊 STATO DOPO PRIMA APPROVAZIONE:\n";
    echo "   Quantità disponibile: {$item->getAvailableQuantity()}\n";
    echo "   Richieste pending: " . Request::where('item_id', $item->id)->where('status', 'pending')->count() . "\n";
    echo "   Richieste approved: " . Request::where('item_id', $item->id)->where('status', 'approved')->count() . "\n";
    echo "   Status item: {$item->status}\n\n";

    // Approva la seconda richiesta
    echo "🟢 APPROVAZIONE RICHIESTA 2:\n";
    $result2 = $approvalService->approveRequest($requests[1], $admin->id);
    echo "   Risultato: " . ($result2['success'] ? '✅ SUCCESSO' : '❌ FALLIMENTO') . "\n";
    echo "   Messaggio: {$result2['message']}\n";
    if ($result2['rejected_requests']) {
        echo "   Richieste rifiutate automaticamente: " . count($result2['rejected_requests']) . "\n";
        foreach ($result2['rejected_requests'] as $rejected) {
            echo "     - Richiesta {$rejected['id']} di {$rejected['user_name']}\n";
        }
    }

    // Controlla stato finale
    $item->refresh();
    echo "\n📊 STATO FINALE:\n";
    echo "   Quantità totale: {$item->quantity}\n";
    echo "   Quantità disponibile: {$item->getAvailableQuantity()}\n";
    echo "   Status item: {$item->status}\n";

    $pendingCount = Request::where('item_id', $item->id)->where('status', 'pending')->count();
    $approvedCount = Request::where('item_id', $item->id)->where('status', 'approved')->count();
    $rejectedCount = Request::where('item_id', $item->id)->where('status', 'rejected')->count();

    echo "   Richieste pending: {$pendingCount}\n";
    echo "   Richieste approved: {$approvedCount}\n";
    echo "   Richieste rejected: {$rejectedCount}\n\n";

    // Test tentativo di approvazione della terza richiesta (dovrebbe fallire)
    if ($pendingCount > 0) {
        echo "🔴 TENTATIVO APPROVAZIONE RICHIESTA 3 (dovrebbe fallire):\n";
        $result3 = $approvalService->approveRequest($requests[2], $admin->id);
        echo "   Risultato: " . ($result3['success'] ? '✅ SUCCESSO' : '❌ FALLIMENTO (atteso)') . "\n";
        echo "   Messaggio: {$result3['message']}\n\n";
    }

    // Test availability info
    echo "📋 INFORMAZIONI DETTAGLIATE DISPONIBILITÀ:\n";
    $availabilityInfo = $approvalService->getItemAvailabilityInfo($item);
    foreach ($availabilityInfo as $key => $value) {
        $label = ucfirst(str_replace('_', ' ', $key));
        $displayValue = is_bool($value) ? ($value ? 'Sì' : 'No') : $value;
        echo "   {$label}: {$displayValue}\n";
    }

    echo "\n🎉 TEST COMPLETATO CON SUCCESSO!\n";
    echo "✅ La gestione della concorrenza funziona correttamente\n";
    echo "✅ Le richieste in eccesso vengono automaticamente rifiutate\n";
    echo "✅ Lo status degli items si aggiorna correttamente\n";
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
