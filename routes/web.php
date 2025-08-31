<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\StatisticsController;
use App\Http\Controllers\WarehouseController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    // Statistics Dashboard - Admin Only
    Route::middleware('can:admin-only')->group(function () {
        Route::get('/statistics', [StatisticsController::class, 'index'])->name('statistics.index');
    });

    // Warehouse routes
    Route::prefix('warehouse')->name('warehouse.')->group(function () {
        Route::get('/', [WarehouseController::class, 'index'])->name('index');
        Route::get('/items', [WarehouseController::class, 'items'])->name('items');
        Route::get('/requests', [WarehouseController::class, 'requests'])->name('requests');
        Route::get('/requests/create', [WarehouseController::class, 'createRequest'])->name('requests.create');
        Route::post('/requests', [WarehouseController::class, 'storeRequest'])->name('requests.store');
        Route::post('/requests/validate-availability', [WarehouseController::class, 'validateAvailability'])->name('requests.validate-availability');

        // Admin only routes
        Route::middleware('can:admin-only')->group(function () {
            // Request management
            Route::patch('/requests/{request}/approve', [WarehouseController::class, 'approveRequest'])->name('requests.approve');
            Route::patch('/requests/{request}/reject', [WarehouseController::class, 'rejectRequest'])->name('requests.reject');
            Route::patch('/requests/{request}/return', [WarehouseController::class, 'returnRequest'])->name('requests.return');

            // Item management (CRUD)
            Route::get('/items/manage', [WarehouseController::class, 'manageItems'])->name('items.manage');
            Route::get('/items/create', [WarehouseController::class, 'createItem'])->name('items.create');
            Route::post('/items', [WarehouseController::class, 'storeItem'])->name('items.store');
            Route::get('/items/{item}/edit', [WarehouseController::class, 'editItem'])->name('items.edit');
            Route::patch('/items/{item}', [WarehouseController::class, 'updateItem'])->name('items.update');
            Route::delete('/items/{item}', [WarehouseController::class, 'destroyItem'])->name('items.destroy');
        });
    });
});
