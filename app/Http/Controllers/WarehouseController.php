<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Request;
use App\Models\User;
use App\Services\RequestApprovalService;
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

        $lowStockItems = Item::where('status', 'available')
            ->get()
            ->filter(function ($item) {
                return $item->getAvailableQuantity() <= 5 && $item->getAvailableQuantity() > 0;
            });

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
        $user = Auth::user();
        if ($user && $user->role === 'admin') {
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
        $users = ($user && $user->role === 'admin') ? \App\Models\User::where('role', 'user')->get() : collect();

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
            ->get()
            ->filter(function ($item) {
                return $item->getAvailableQuantity() > 0;
            })
            ->values(); // Reset keys after filter

        $categories = Item::distinct()->pluck('category')->sort()->values();

        return Inertia::render('Warehouse/CreateRequest', [
            'availableItems' => $availableItems,
            'categories' => $categories,
        ]);
    }

    /**
     * Store a newly created request.
     */
    public function storeRequest(HttpRequest $request)
    {
        // Validazione base comune
        $baseRules = [
            'request_type' => 'required|in:existing_item,purchase_request',
            'reason' => 'required|string|max:500',
            'notes' => 'nullable|string|max:1000',
            'priority' => 'required|in:low,medium,high,urgent',
            'quantity_requested' => 'required|integer|min:1',
        ];

        // Validazione specifica per tipo di richiesta
        if ($request->input('request_type') === 'existing_item') {
            $specificRules = [
                'item_id' => 'required|exists:items,id',
                'start_date' => 'required|date|after_or_equal:today',
                'end_date' => 'required|date|after:start_date',
            ];
        } else { // purchase_request
            $specificRules = [
                'item_id' => 'nullable',
                'item_name' => 'required|string|max:255',
                'item_description' => 'required|string|max:1000',
                'item_category' => 'required|string|max:100',
                'item_brand' => 'nullable|string|max:100',
                'estimated_cost' => 'required|numeric|min:0',
                'supplier_info' => 'nullable|string|max:500',
                'justification' => 'required|string|max:1000',
                'start_date' => 'nullable|date|after_or_equal:today',
                'end_date' => 'nullable|date|after:start_date',
            ];
        }

        $validated = $request->validate(array_merge($baseRules, $specificRules));

        // Controlli specifici per item esistenti
        if ($validated['request_type'] === 'existing_item') {
            $item = Item::findOrFail($validated['item_id']);
            $availableQuantity = $item->getAvailableQuantity();

            if ($validated['quantity_requested'] > $availableQuantity) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['quantity_requested' => "Quantità richiesta non disponibile. Disponibili: {$availableQuantity}"]);
            }

            // Controllo sovrapposizioni temporali
            if (\App\Models\Request::hasOverlappingRequests(
                $validated['item_id'],
                $validated['start_date'],
                $validated['end_date']
            )) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['start_date' => 'Il periodo richiesto si sovrappone con altre richieste approvate per questo item.']);
            }
        }

        $validated['user_id'] = Auth::id();

        \App\Models\Request::create($validated);

        $message = $validated['request_type'] === 'purchase_request'
            ? 'Richiesta di acquisto inviata con successo! In attesa di valutazione.'
            : 'Richiesta inviata con successo! In attesa di approvazione.';

        return redirect()->route('warehouse.requests')
            ->with('success', $message);
    }

    /**
     * Approve a request (Admin only).
     */
    public function approveRequest(\App\Models\Request $request, RequestApprovalService $approvalService)
    {
        $user = Auth::user();
        abort_unless($user && $user->role === 'admin', 403);

        // Usa il service per gestire l'approvazione con concorrenza
        $result = $approvalService->approveRequest($request, Auth::id());

        if ($result['success']) {
            $flashType = count($result['rejected_requests']) > 0 ? 'warning' : 'success';
            return redirect()->back()->with($flashType, $result['message']);
        } else {
            return redirect()->back()->with('error', $result['message']);
        }
    }

    /**
     * Reject a request (Admin only).
     */
    public function rejectRequest(HttpRequest $httpRequest, \App\Models\Request $request)
    {
        $user = Auth::user();
        abort_unless($user && $user->role === 'admin', 403);

        $validated = $httpRequest->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $request->update([
            'status' => 'rejected',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        return redirect()->back()
            ->with('success', 'Richiesta rifiutata. L\'utente è stato notificato del motivo.');
    }

    /**
     * Mark a request as returned.
     */
    public function returnRequest(\App\Models\Request $request)
    {
        $user = Auth::user();
        abort_unless($user && $user->role === 'admin', 403);

        // Verifica che la richiesta sia approvata
        if ($request->status !== 'approved') {
            return redirect()->back()
                ->with('error', 'Solo le richieste approvate possono essere marchiate come restituite.');
        }

        // Aggiorna semplicemente la richiesta a "returned"
        // La quantità dell'item non cambia più fisicamente
        $request->update([
            'status' => 'returned',
            'returned_at' => now(),
        ]);

        // Aggiorna lo status dell'item (ora disponibile di nuovo)
        $request->item->calculateAndUpdateStatus();

        return redirect()->back()
            ->with('success', 'Articolo contrassegnato come restituito con successo.');
    }

    // =====================================
    // CRUD OPERATIONS FOR ITEMS (Admin Only)
    // =====================================

    /**
     * Display a listing of items for admin management.
     */
    public function manageItems(HttpRequest $request): Response
    {
        $user = Auth::user();
        abort_unless($user && $user->role === 'admin', 403);

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
        $user = Auth::user();
        abort_unless($user && $user->role === 'admin', 403);

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
        $user = Auth::user();
        abort_unless($user && $user->role === 'admin', 403);

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
        $user = Auth::user();
        abort_unless($user && $user->role === 'admin', 403);

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
        $user = Auth::user();
        abort_unless($user && $user->role === 'admin', 403);

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
        $user = Auth::user();
        abort_unless($user && $user->role === 'admin', 403);

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
