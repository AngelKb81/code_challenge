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

// Test queries
try {
    $itemsCount = Capsule::table('items')->count();
    echo "Total items: $itemsCount\n";

    $items = Capsule::table('items')->select('id', 'name', 'status', 'quantity')->take(5)->get();
    foreach ($items as $item) {
        echo "- {$item->id}: {$item->name} ({$item->status}, qty: {$item->quantity})\n";
    }

    $requestsCount = Capsule::table('requests')->count();
    echo "\nTotal requests: $requestsCount\n";

    // Mostra stato delle richieste
    $requests = Capsule::table('requests')
        ->select('id', 'status', 'user_id', 'item_id', 'created_at')
        ->orderBy('created_at', 'desc')
        ->take(5)
        ->get();

    echo "Recent requests:\n";
    foreach ($requests as $request) {
        echo "- Request {$request->id}: status={$request->status}, user={$request->user_id}, item={$request->item_id}\n";
    }

    // Conta richieste per stato
    $statusCounts = Capsule::table('requests')
        ->select('status', Capsule::raw('count(*) as count'))
        ->groupBy('status')
        ->get();

    echo "\nRequests by status:\n";
    foreach ($statusCounts as $status) {
        echo "- {$status->status}: {$status->count}\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
