<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Request;
use App\Models\User;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class WarehouseController extends Controller
{
    /**
     * Display the warehouse dashboard.
     */
    public function index(): Response
    {
        $stats = [
            'totalItems' => Item::count(),
            'availableItems' => Item::where('status', 'available')->count(),
            'pendingRequests' => Request::where('status', 'pending')->count(),
            'activeRequests' => Request::where('status', 'approved')->count(),
        ];

        $recentRequests = Request::with(['user', 'item', 'approver'])
            ->latest()
            ->take(5)
            ->get();

        $lowStockItems = Item::where('quantity', '<=', 5)
            ->where('status', 'available')
            ->get();

        return Inertia::render('Warehouse/Dashboard', [
            'stats' => $stats,
            'recentRequests' => $recentRequests,
            'lowStockItems' => $lowStockItems,
        ]);
    }

    /**
     * Display a listing of items.
     */
    public function items(HttpRequest $request): Response
    {
        $query = Item::query();

        // Filtri di ricerca
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('brand', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%")
                    ->orWhere('serial_number', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->get('category'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        $items = $query->orderBy('name')->paginate(12);

        $categories = Item::distinct()->pluck('category')->sort()->values();
        $statuses = ['available', 'not_available', 'maintenance', 'reserved'];

        return Inertia::render('Warehouse/Items', [
            'items' => $items,
            'categories' => $categories,
            'statuses' => $statuses,
            'filters' => $request->only(['search', 'category', 'status']),
        ]);
    }

    /**
     * Display a listing of requests.
     */
    public function requests(HttpRequest $request): Response
    {
        $query = Request::with(['user', 'item', 'approver']);

        // Filtri per gli admin
        if (Auth::user()->isAdmin()) {
            if ($request->filled('status')) {
                $query->where('status', $request->get('status'));
            }

            if ($request->filled('priority')) {
                $query->where('priority', $request->get('priority'));
            }

            if ($request->filled('user')) {
                $query->where('user_id', $request->get('user'));
            }
        } else {
            // Gli utenti normali vedono solo le proprie richieste
            $query->where('user_id', Auth::id());
        }

        $requests = $query->orderBy('created_at', 'desc')->paginate(10);

        $statuses = ['pending', 'approved', 'rejected', 'in_use', 'returned', 'overdue'];
        $priorities = ['low', 'medium', 'high', 'urgent'];
        $users = Auth::user()->isAdmin() ? \App\Models\User::where('role', 'user')->get() : collect();

        return Inertia::render('Warehouse/Requests', [
            'requests' => $requests,
            'statuses' => $statuses,
            'priorities' => $priorities,
            'users' => $users,
            'filters' => $request->only(['status', 'priority', 'user']),
        ]);
    }

    /**
     * Show the form for creating a new request.
     */
    public function createRequest(): Response
    {
        $availableItems = Item::where('status', 'available')
            ->where('quantity', '>', 0)
            ->orderBy('name')
            ->get();

        return Inertia::render('Warehouse/CreateRequest', [
            'availableItems' => $availableItems,
        ]);
    }

    /**
     * Store a newly created request.
     */
    public function storeRequest(HttpRequest $request)
    {
        $validated = $request->validate([
            'item_id' => 'required|exists:items,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after:start_date',
            'reason' => 'required|string|max:500',
            'notes' => 'nullable|string|max:1000',
            'priority' => 'required|in:low,medium,high,urgent',
        ]);

        $validated['user_id'] = Auth::id();

        Request::create($validated);

        return redirect()->route('warehouse.requests')
            ->with('success', 'Richiesta inviata con successo! In attesa di approvazione.');
    }

    /**
     * Approve a request (Admin only).
     */
    public function approveRequest(Request $warehouseRequest)
    {
        abort_unless(Auth::user()->isAdmin(), 403);

        $warehouseRequest->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        return redirect()->back()
            ->with('success', 'Richiesta approvata con successo!');
    }

    /**
     * Reject a request (Admin only).
     */
    public function rejectRequest(HttpRequest $request, Request $warehouseRequest)
    {
        abort_unless(Auth::user()->isAdmin(), 403);

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $warehouseRequest->update([
            'status' => 'rejected',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        return redirect()->back()
            ->with('success', 'Richiesta rifiutata.');
    }

    /**
     * Mark a request as returned.
     */
    public function returnRequest(Request $warehouseRequest)
    {
        abort_unless(Auth::user()->isAdmin(), 403);

        $warehouseRequest->update([
            'status' => 'returned',
            'returned_at' => now(),
        ]);

        return redirect()->back()
            ->with('success', 'Materiale contrassegnato come restituito.');
    }

    // =====================================
    // CRUD OPERATIONS FOR ITEMS (Admin Only)
    // =====================================

    /**
     * Display a listing of items for admin management.
     */
    public function manageItems(HttpRequest $request): Response
    {
        abort_unless(Auth::user()->isAdmin(), 403);

        $query = Item::query();

        // Filtri di ricerca
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('brand', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%")
                  ->orWhere('serial_number', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->get('category'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        $items = $query->orderBy('name')->paginate(15);

        $categories = Item::distinct()->pluck('category')->sort()->values();
        $statuses = ['available', 'not_available', 'maintenance', 'reserved'];

        return Inertia::render('Warehouse/ManageItems', [
            'items' => $items,
            'categories' => $categories,
            'statuses' => $statuses,
            'filters' => $request->only(['search', 'category', 'status']),
        ]);
    }

    /**
     * Show the form for creating a new item.
     */
    public function createItem(): Response
    {
        abort_unless(Auth::user()->isAdmin(), 403);

        $categories = Item::distinct()->pluck('category')->sort()->values();
        $statuses = ['available', 'not_available', 'maintenance', 'reserved'];

        return Inertia::render('Warehouse/CreateItem', [
            'categories' => $categories,
            'statuses' => $statuses,
        ]);
    }

    /**
     * Store a newly created item.
     */
    public function storeItem(HttpRequest $request)
    {
        abort_unless(Auth::user()->isAdmin(), 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'status' => 'required|in:available,not_available,maintenance,reserved',
            'brand' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:1000',
            'quantity' => 'required|integer|min:0',
            'serial_number' => 'nullable|string|max:100|unique:items,serial_number',
            'location' => 'nullable|string|max:255',
            'purchase_price' => 'nullable|numeric|min:0',
            'purchase_date' => 'nullable|date',
            'warranty_expiry' => 'nullable|date|after:purchase_date',
            'notes' => 'nullable|string|max:1000',
        ]);

        Item::create($validated);

        return redirect()->route('warehouse.items.manage')
            ->with('success', 'Articolo creato con successo!');
    }

    /**
     * Show the form for editing an item.
     */
    public function editItem(Item $item): Response
    {
        abort_unless(Auth::user()->isAdmin(), 403);

        $categories = Item::distinct()->pluck('category')->sort()->values();
        $statuses = ['available', 'not_available', 'maintenance', 'reserved'];

        return Inertia::render('Warehouse/EditItem', [
            'item' => $item,
            'categories' => $categories,
            'statuses' => $statuses,
        ]);
    }

    /**
     * Update an existing item.
     */
    public function updateItem(HttpRequest $request, Item $item)
    {
        abort_unless(Auth::user()->isAdmin(), 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'status' => 'required|in:available,not_available,maintenance,reserved',
            'brand' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:1000',
            'quantity' => 'required|integer|min:0',
            'serial_number' => 'nullable|string|max:100|unique:items,serial_number,' . $item->id,
            'location' => 'nullable|string|max:255',
            'purchase_price' => 'nullable|numeric|min:0',
            'purchase_date' => 'nullable|date',
            'warranty_expiry' => 'nullable|date|after:purchase_date',
            'notes' => 'nullable|string|max:1000',
        ]);

        $item->update($validated);

        return redirect()->route('warehouse.items.manage')
            ->with('success', 'Articolo aggiornato con successo!');
    }

    /**
     * Delete an item.
     */
    public function destroyItem(Item $item)
    {
        abort_unless(Auth::user()->isAdmin(), 403);

        // Verifica se ci sono richieste attive per questo item
        $activeRequests = Request::where('item_id', $item->id)
            ->whereIn('status', ['pending', 'approved', 'in_use'])
            ->count();

        if ($activeRequests > 0) {
            return redirect()->back()
                ->with('error', 'Impossibile eliminare l\'articolo: ci sono richieste attive associate.');
        }

        $item->delete();

        return redirect()->route('warehouse.items.manage')
            ->with('success', 'Articolo eliminato con successo!');
    }
}
