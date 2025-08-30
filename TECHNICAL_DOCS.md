# 🔧 DOCUMENTAZIONE TECNICA - Code Challenge

## 📋 **Indice**
1. [Decisioni Architetturali](#decisioni-architetturali)
2. [Architettura Sistema](#architettura-sistema)
3. [Database Design](#database-design)  
4. [Autorizzazioni & Sicurezza](#autorizzazioni--sicurezza)
5. [API Routes](#api-routes)
6. [Frontend Components](#frontend-components)
7. [Business Logic](#business-logic)
8. [Performance & Optimization](#performance--optimization)
9. [Testing & Debugging](#testing--debugging)

---

## 🎯 **Decisioni Architetturali**

### **Inventory Management Approach: Quantity-Based vs Asset-Based**

#### **Analisi del Problema**
Per sistemi di warehouse management esistono due approcci principali:

**1. Quantity-Based (Implementato)**
- Gestione per quantità aggregate
- Un record per tipologia di item
- Calcolo disponibilità: `quantity - assigned_quantity`

**2. Asset-Based (Enterprise)**
- Gestione granulare per singolo asset
- Tracciabilità individuale con seriali
- Relazioni complesse tra modelli e istanze

#### **Matrice di Decisione**

| Criterio | Quantity-Based | Asset-Based | Peso | Scelta |
|----------|----------------|-------------|------|--------|
| **Complessità Sviluppo** | ⭐⭐⭐⭐⭐ | ⭐⭐ | Alto | ✅ Quantity |
| **Time to Market** | ⭐⭐⭐⭐⭐ | ⭐⭐ | Alto | ✅ Quantity |
| **Performance** | ⭐⭐⭐⭐⭐ | ⭐⭐⭐ | Medio | ✅ Quantity |
| **Tracciabilità** | ⭐⭐ | ⭐⭐⭐⭐⭐ | Basso* | ❌ Asset |
| **Scalabilità Enterprise** | ⭐⭐⭐ | ⭐⭐⭐⭐⭐ | Basso* | ❌ Asset |
| **Adeguatezza Contesto** | ⭐⭐⭐⭐⭐ | ⭐⭐ | Alto | ✅ Quantity |

*Peso basso per contesto demo/test

#### **Schema Implementato (Quantity-Based)**
```sql
-- Approccio semplificato per aggregati
items {
    id, name, category, brand,
    quantity INT,                    -- Quantità totale
    status ENUM('available', ...),   -- Stato operativo
    -- available_quantity calcolato dinamicamente
}

requests {
    id, user_id, item_id,
    quantity INT,                    -- Quantità richiesta
    status ENUM('pending', 'approved', ...)
}

-- Calcolo disponibilità
available_quantity = items.quantity - SUM(approved_requests.quantity)
```

#### **Schema Alternativo (Asset-Based)**
```sql
-- Approccio enterprise con tracciabilità granulare
categories { id, name, description }
brands { id, name }
models { id, category_id, brand_id, name, specifications }

asset_instances {
    id, model_id, serial_number,
    purchase_date, condition,
    status ENUM('available', 'assigned', 'maintenance', ...),
    location, notes
}

requests { id, user_id, model_id, quantity_requested }
request_assignments { request_id, asset_instance_id, assigned_date }
```

#### **Pros/Cons Analisi Dettagliata**

**✅ Vantaggi Quantity-Based (Scelto)**
- **Semplicità**: 2 tabelle principali vs 6+ tabelle
- **Performance**: Query dirette, meno JOIN, calcoli veloci
- **Sviluppo Rapido**: 80% meno tempo di sviluppo
- **UI Intuitiva**: Interfacce meno complesse
- **Manutenibilità**: Codice più pulito e comprensibile
- **Adeguato al Contesto**: Perfetto per demo/prototipi

**❌ Limitazioni Quantity-Based**
- **Tracciabilità**: Non posso tracciare singoli asset
- **Granularità**: Impossibile gestire stati di singoli item
- **Seriali**: Perdita informazioni sui numeri seriali
- **Audit**: Trail limitato per compliance enterprise
- **Manutenzione**: Non posso marcare singoli asset "in riparazione"

**✅ Vantaggi Asset-Based (Non implementato)**
- **Tracciabilità Completa**: Ogni asset tracciato individualmente
- **Compliance**: Rispetta standard enterprise
- **Flessibilità**: Gestione granulare di stati e condizioni
- **Reporting**: Analytics dettagliate per asset
- **Scalabilità**: Adatto per organizzazioni complesse

**❌ Limitazioni Asset-Based**
- **Complessità**: Architettura molto più complessa
- **Performance**: Query pesanti con molti JOIN
- **Over-engineering**: Eccessivo per il contesto attuale
- **Sviluppo**: Richiede 3-4x più tempo
- **UI Complexity**: Interfacce molto più articolate

#### **Giustificazione della Scelta**

**Contesto Progetto**: Code challenge / Demo application
**Requisiti**: Dimostrare competenze full-stack moderne
**Timeline**: Sviluppo rapido richiesto
**Compliance**: Non necessaria per ambiente di test

**Decisione**: Quantity-Based approach per massimizzare:
- Velocità di sviluppo
- Chiarezza del codice  
- Performance del sistema
- Semplicità di manutenzione

#### **Percorso di Migrazione Futura**

Se il sistema dovesse evolvere verso un approccio enterprise:

```sql
-- Step 1: Normalizzazione Master Data
CREATE TABLE categories, brands, models;
ALTER TABLE items ADD model_id;

-- Step 2: Asset Instances
CREATE TABLE asset_instances (
    id, model_id, serial_number, 
    item_id, -- temporary bridge
    status, condition, location
);

-- Step 3: Request Assignments  
CREATE TABLE request_assignments (
    request_id, asset_instance_id,
    assigned_date, returned_date
);

-- Step 4: Data Migration
-- Migrazione graduale mantenendo compatibilità

-- Step 5: Cleanup
-- Rimozione colonne quantity da items
-- Aggiornamento logica business
```

#### **Lessons Learned**

1. **Context is King**: L'architettura deve servire il contesto
2. **YAGNI Principle**: "You Aren't Gonna Need It" - non over-engineer
3. **Incremental Evolution**: Meglio partire semplice e evolvere
4. **Performance vs Features**: Bilanciare requisiti tecnici e funzionali
5. **Documentation**: Documentare le scelte per future evoluzioni

---

## 🏗️ **Architettura Sistema**

### Stack Tecnologico Dettagliato

#### Backend - Laravel 11
```php
Framework: Laravel 11.x
PHP: ^8.2
Database: MySQL 8.0+
ORM: Eloquent con relazioni
Cache: File-based (estendibile Redis)
Session: Database driver
Queue: Sync (estendibile Redis/Database)
```

#### Frontend - Vue 3 + Inertia.js
```javascript
Vue: 3.x (Composition API)
Inertia.js: ^2.1.3 (SPA Bridge)
Build Tool: Vite ^6.0.11
CSS Framework: Tailwind CSS ^3.4.13
Icons: Heroicons (via Tailwind)
HTTP Client: Axios ^1.7.4
```

#### Development Tools
```json
Hot Module Replacement: Vite HMR
Asset Compilation: Vite + Laravel Plugin
Package Manager: NPM
Process Manager: Concurrently
Version Control: Git
```

### Architettura MVC Estesa

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Frontend      │    │   Backend       │    │   Database      │
│   Vue 3 SPA     │◄──►│   Laravel 11    │◄──►│   MySQL         │
│                 │    │                 │    │                 │
│ • Components    │    │ • Controllers   │    │ • Migrations    │
│ • Composables   │    │ • Models        │    │ • Seeders       │ 
│ • Layouts       │    │ • Middleware    │    │ • Relations     │
│ • Pages         │    │ • Gates         │    │ • Indexes       │
└─────────────────┘    └─────────────────┘    └─────────────────┘
        │                       │                       │
        └───────────────────────┼───────────────────────┘
                                │
                    ┌─────────────────┐
                    │   Inertia.js    │
                    │   Bridge        │
                    │                 │  
                    │ • Route Model   │
                    │ • Form Helpers  │
                    │ • State Mgmt    │
                    │ • SSR Ready     │
                    └─────────────────┘
```

---

## 🗄️ **Database Design**

### Schema Relazionale Completo

#### Tabella Users
```sql
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') DEFAULT 'user',
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    INDEX idx_email (email),
    INDEX idx_role (role)
);
```

#### Tabella Items (Articoli Magazzino)
```sql
CREATE TABLE items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    category VARCHAR(100) NOT NULL,
    quantity INT NOT NULL DEFAULT 0,
    unit_price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    location VARCHAR(255),
    status ENUM('available', 'low_stock', 'out_of_stock') DEFAULT 'available',
    min_quantity INT DEFAULT 5,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    INDEX idx_category (category),
    INDEX idx_status (status),
    INDEX idx_name (name),
    FULLTEXT idx_search (name, description)
);
```

#### Tabella Requests (Richieste)
```sql
CREATE TABLE requests (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    item_id BIGINT UNSIGNED NOT NULL,
    quantity_requested INT NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    notes TEXT,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (item_id) REFERENCES items(id) ON DELETE CASCADE,
    INDEX idx_user_status (user_id, status),
    INDEX idx_item_status (item_id, status)
);
```

### Relazioni Eloquent

#### User Model
```php
class User extends Authenticatable
{
    // Relazioni
    public function requests(): HasMany
    {
        return $this->hasMany(Request::class);
    }
    
    // Accessors & Mutators
    public function getIsAdminAttribute(): bool
    {
        return $this->role === 'admin';
    }
    
    // Scopes
    public function scopeAdmins($query)
    {
        return $query->where('role', 'admin');
    }
    
    public function scopeUsers($query)
    {
        return $query->where('role', 'user');
    }
}
```

#### Item Model
```php
class Item extends Model
{
    // Calcolo automatico stato
    protected static function booted()
    {
        static::saving(function ($item) {
            $item->status = $item->calculateStatus();
        });
    }
    
    public function calculateStatus(): string
    {
        if ($this->quantity <= 0) {
            return 'out_of_stock';
        }
        
        if ($this->quantity <= $this->min_quantity) {
            return 'low_stock';
        }
        
        return 'available';
    }
    
    // Relazioni
    public function requests(): HasMany
    {
        return $this->hasMany(Request::class);
    }
    
    // Scopes
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }
    
    public function scopeLowStock($query)
    {
        return $query->where('status', 'low_stock');
    }
    
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }
}
```

#### Request Model
```php
class Request extends Model
{
    // Relazioni
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
    
    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
    
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }
}
```

---

## 🔐 **Autorizzazioni & Sicurezza**

### Sistema Gates Laravel

```php
// app/Providers/AppServiceProvider.php
public function boot(): void
{
    // Gate per operazioni admin
    Gate::define('admin-only', function (User $user) {
        return $user->role === 'admin';
    });
    
    // Gate per operazioni utente
    Gate::define('user-operations', function (User $user) {
        return in_array($user->role, ['admin', 'user']);
    });
    
    // Gate per visualizzazione magazzino
    Gate::define('view-warehouse', function (User $user) {
        return $user->role === 'admin';
    });
    
    // Gate per modifica articoli
    Gate::define('manage-items', function (User $user) {
        return $user->role === 'admin';
    });
}
```

### Middleware Protection

```php
// routes/web.php - Protezione route magazzino
Route::middleware(['auth', 'can:admin-only'])->group(function () {
    Route::get('/warehouse', [WarehouseController::class, 'index'])->name('warehouse.index');
    Route::get('/warehouse/manage', [WarehouseController::class, 'manageItems'])->name('warehouse.manage');
    Route::get('/warehouse/create', [WarehouseController::class, 'createItem'])->name('warehouse.create');
    Route::post('/warehouse', [WarehouseController::class, 'storeItem'])->name('warehouse.store');
    Route::get('/warehouse/{item}/edit', [WarehouseController::class, 'editItem'])->name('warehouse.edit');
    Route::patch('/warehouse/{item}', [WarehouseController::class, 'updateItem'])->name('warehouse.update');
    Route::delete('/warehouse/{item}', [WarehouseController::class, 'destroyItem'])->name('warehouse.destroy');
});
```

### Validazione Controller

```php
// WarehouseController - Validazione storeItem
public function storeItem(Request $request)
{
    // Controllo autorizzazione
    if (!Auth::user()->role === 'admin') {
        abort(403, 'Accesso negato');
    }
    
    // Validazione dati
    $validated = $request->validate([
        'name' => 'required|string|max:255|unique:items,name',
        'description' => 'nullable|string|max:1000',
        'category' => 'required|string|max:100',
        'quantity' => 'required|integer|min:0',
        'unit_price' => 'required|numeric|min:0',
        'location' => 'nullable|string|max:255',
        'min_quantity' => 'required|integer|min:1|max:1000'
    ], [
        'name.required' => 'Il nome è obbligatorio',
        'name.unique' => 'Esiste già un articolo con questo nome',
        'quantity.min' => 'La quantità non può essere negativa',
        'unit_price.min' => 'Il prezzo non può essere negativo'
    ]);
    
    // Business logic
    $item = Item::create($validated);
    
    return redirect()->route('warehouse.manage')->with('success', 'Articolo creato con successo');
}
```

---

## 🚀 **API Routes**

### Route Structure

```php
// Autenticazione
POST   /login                     # Login utente
POST   /logout                    # Logout utente
GET    /register                  # Form registrazione
POST   /register                  # Registrazione utente

// Dashboard
GET    /dashboard                 # Dashboard principale

// Profilo  
GET    /profile                   # Visualizza profilo
PATCH  /profile                   # Aggiorna profilo
DELETE /profile                   # Elimina profilo

// Magazzino (Admin Only)
GET    /warehouse                 # Dashboard magazzino
GET    /warehouse/manage          # Lista articoli admin
GET    /warehouse/create          # Form creazione articolo
POST   /warehouse                 # Store nuovo articolo
GET    /warehouse/{item}/edit     # Form modifica articolo
PATCH  /warehouse/{item}          # Update articolo
DELETE /warehouse/{item}          # Elimina articolo
```

### Controller Methods Dettaglio

```php
class WarehouseController extends Controller
{
    public function index()
    {
        // Dashboard magazzino con statistiche
        $stats = [
            'total_items' => Item::count(),
            'low_stock_items' => Item::lowStock()->count(),
            'out_of_stock_items' => Item::where('status', 'out_of_stock')->count(),
            'total_value' => Item::sum(DB::raw('quantity * unit_price'))
        ];
        
        return Inertia::render('Warehouse/Index', compact('stats'));
    }
    
    public function manageItems(Request $request)
    {
        // Query builder con filtri
        $query = Item::query();
        
        // Filtro ricerca
        if ($search = $request->get('search')) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }
        
        // Filtro categoria
        if ($category = $request->get('category')) {
            $query->where('category', $category);
        }
        
        // Filtro stato
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }
        
        // Paginazione
        $items = $query->orderBy('name')
                      ->paginate(15)
                      ->withQueryString();
        
        // Categorie per filtro
        $categories = Item::distinct()->pluck('category');
        
        return Inertia::render('Warehouse/ManageItems', [
            'items' => $items,
            'categories' => $categories,
            'filters' => $request->only(['search', 'category', 'status'])
        ]);
    }
    
    public function storeItem(Request $request)
    {
        $validated = $this->validateItem($request);
        
        try {
            $item = Item::create($validated);
            
            return redirect()->route('warehouse.manage')
                           ->with('success', "Articolo '{$item->name}' creato con successo");
                           
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Errore durante la creazione dell\'articolo'])
                        ->withInput();
        }
    }
    
    private function validateItem(Request $request, ?Item $item = null): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'category' => 'required|string|max:100',
            'quantity' => 'required|integer|min:0',
            'unit_price' => 'required|numeric|min:0',
            'location' => 'nullable|string|max:255',
            'min_quantity' => 'required|integer|min:1|max:1000'
        ];
        
        // Unique validation per update
        if ($item) {
            $rules['name'] .= ',name,' . $item->id;
        } else {
            $rules['name'] .= '|unique:items,name';
        }
        
        return $request->validate($rules);
    }
}
```

---

## 🎨 **Frontend Components**

### Vue 3 Composition API Structure

#### ManageItems.vue - Componente Principale
```vue
<script setup>
import { ref, computed, watch } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

// Props
const props = defineProps({
    items: Object,
    categories: Array,
    filters: Object
})

// Reactive state
const search = ref(props.filters.search || '')
const selectedCategory = ref(props.filters.category || '')
const selectedStatus = ref(props.filters.status || '')
const showDeleteModal = ref(false)
const itemToDelete = ref(null)

// Computed
const hasFilters = computed(() => {
    return search.value || selectedCategory.value || selectedStatus.value
})

// Methods
const applyFilters = () => {
    router.get(route('warehouse.manage'), {
        search: search.value,
        category: selectedCategory.value,
        status: selectedStatus.value
    }, {
        preserveState: true,
        preserveScroll: true
    })
}

const clearFilters = () => {
    search.value = ''
    selectedCategory.value = ''
    selectedStatus.value = ''
    applyFilters()
}

const confirmDelete = (item) => {
    itemToDelete.value = item
    showDeleteModal.value = true
}

const deleteItem = () => {
    if (itemToDelete.value) {
        router.delete(route('warehouse.destroy', itemToDelete.value.id), {
            onSuccess: () => {
                showDeleteModal.value = false
                itemToDelete.value = null
            }
        })
    }
}

// Watchers
watch([search, selectedCategory, selectedStatus], () => {
    // Debounce search
    clearTimeout(window.searchTimeout)
    window.searchTimeout = setTimeout(applyFilters, 300)
})
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Gestione Articoli Magazzino
                </h2>
                <Link 
                    :href="route('warehouse.create')"
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
                >
                    + Nuovo Articolo
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Filtri -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Ricerca
                                </label>
                                <input
                                    v-model="search"
                                    type="text"
                                    placeholder="Nome o descrizione..."
                                    class="w-full border-gray-300 rounded-md"
                                >
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Categoria
                                </label>
                                <select v-model="selectedCategory" class="w-full border-gray-300 rounded-md">
                                    <option value="">Tutte le categorie</option>
                                    <option v-for="category in categories" :key="category" :value="category">
                                        {{ category }}
                                    </option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Stato
                                </label>
                                <select v-model="selectedStatus" class="w-full border-gray-300 rounded-md">
                                    <option value="">Tutti gli stati</option>
                                    <option value="available">Disponibile</option>
                                    <option value="low_stock">Scorte basse</option>
                                    <option value="out_of_stock">Esaurito</option>
                                </select>
                            </div>
                            
                            <div class="flex items-end">
                                <button
                                    v-if="hasFilters"
                                    @click="clearFilters"
                                    class="w-full bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded"
                                >
                                    Pulisci Filtri
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabella Articoli -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Articolo
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Categoria
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Quantità
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Prezzo
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Stato
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Azioni
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="item in items.data" :key="item.id" class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ item.name }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                {{ item.description }}
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ item.category }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ item.quantity }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        €{{ item.unit_price }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="{
                                            'bg-green-100 text-green-800': item.status === 'available',
                                            'bg-yellow-100 text-yellow-800': item.status === 'low_stock',
                                            'bg-red-100 text-red-800': item.status === 'out_of_stock'
                                        }" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                                            {{ getStatusLabel(item.status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <Link
                                            :href="route('warehouse.edit', item.id)"
                                            class="text-indigo-600 hover:text-indigo-900 mr-3"
                                        >
                                            Modifica
                                        </Link>
                                        <button
                                            @click="confirmDelete(item)"
                                            class="text-red-600 hover:text-red-900"
                                        >
                                            Elimina
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginazione -->
                    <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                        <Pagination :links="items.links" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Conferma Eliminazione -->
        <ConfirmModal
            :show="showDeleteModal"
            @close="showDeleteModal = false"
            @confirm="deleteItem"
            title="Conferma Eliminazione"
            :message="`Sei sicuro di voler eliminare l'articolo '${itemToDelete?.name}'?`"
        />
    </AuthenticatedLayout>
</template>
```

### Composables Riutilizzabili

```javascript
// composables/useItems.js
import { ref, reactive } from 'vue'
import { router } from '@inertiajs/vue3'

export function useItems() {
    const loading = ref(false)
    const errors = ref({})
    
    const createItem = async (itemData) => {
        loading.value = true
        errors.value = {}
        
        try {
            await router.post(route('warehouse.store'), itemData, {
                onError: (errors) => {
                    errors.value = errors
                },
                onFinish: () => {
                    loading.value = false
                }
            })
        } catch (error) {
            console.error('Errore creazione articolo:', error)
            loading.value = false
        }
    }
    
    const updateItem = async (itemId, itemData) => {
        loading.value = true
        errors.value = {}
        
        try {
            await router.patch(route('warehouse.update', itemId), itemData, {
                onError: (errors) => {
                    errors.value = errors
                },
                onFinish: () => {
                    loading.value = false
                }
            })
        } catch (error) {
            console.error('Errore aggiornamento articolo:', error)
            loading.value = false
        }
    }
    
    return {
        loading,
        errors,
        createItem,
        updateItem
    }
}
```

---

## 🧠 **Business Logic**

### Gestione Stati Articoli

```php
// Item Model - Calcolo automatico stato
public function calculateStatus(): string
{
    // Logica business per determinare stato
    if ($this->quantity <= 0) {
        return 'out_of_stock';
    }
    
    if ($this->quantity <= $this->min_quantity) {
        return 'low_stock';
    }
    
    return 'available';
}

// Observer per aggiornamento automatico
protected static function booted()
{
    static::saving(function ($item) {
        $item->status = $item->calculateStatus();
    });
    
    static::created(function ($item) {
        // Log creazione articolo
        \Log::info("Nuovo articolo creato: {$item->name}");
    });
    
    static::updated(function ($item) {
        // Notifica se quantità critica
        if ($item->status === 'low_stock' && $item->getOriginal('status') !== 'low_stock') {
            // Trigger notifica admin
            event(new \App\Events\LowStockAlert($item));
        }
    });
}
```

### Validazione Avanzata

```php
// Custom Form Request
class StoreItemRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->role === 'admin';
    }
    
    public function rules()
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:items,name',
                'regex:/^[a-zA-Z0-9\s\-\_\.]+$/'  // Solo caratteri alfanumerici
            ],
            'description' => 'nullable|string|max:1000',
            'category' => [
                'required',
                'string',
                'max:100',
                Rule::in($this->getAllowedCategories())  // Solo categorie predefinite
            ],
            'quantity' => 'required|integer|min:0|max:999999',
            'unit_price' => 'required|numeric|min:0|max:999999.99',
            'location' => 'nullable|string|max:255',
            'min_quantity' => 'required|integer|min:1|max:1000|lte:quantity'  // min_quantity <= quantity
        ];
    }
    
    public function messages()
    {
        return [
            'name.required' => 'Il nome dell\'articolo è obbligatorio',
            'name.unique' => 'Esiste già un articolo con questo nome',
            'name.regex' => 'Il nome può contenere solo lettere, numeri e i caratteri: - _ .',
            'category.in' => 'La categoria selezionata non è valida',
            'quantity.min' => 'La quantità non può essere negativa',
            'unit_price.min' => 'Il prezzo non può essere negativo',
            'min_quantity.lte' => 'La quantità minima non può essere superiore alla quantità disponibile'
        ];
    }
    
    private function getAllowedCategories(): array
    {
        return [
            'Elettronica',
            'Casa',
            'Ufficio',
            'Gaming',
            'Abbigliamento',
            'Sport',
            'Libri',
            'Altro'
        ];
    }
}
```

---

## ⚡ **Performance & Optimization**

### Database Optimization

```php
// Migration con indici ottimizzati
Schema::create('items', function (Blueprint $table) {
    $table->id();
    $table->string('name')->index();  // Indice per ricerche frequenti
    $table->text('description');
    $table->string('category', 100)->index();  // Indice per filtri
    $table->integer('quantity')->index();  // Indice per ordinamenti
    $table->decimal('unit_price', 10, 2);
    $table->string('location')->nullable();
    $table->enum('status', ['available', 'low_stock', 'out_of_stock'])->index();
    $table->integer('min_quantity')->default(5);
    $table->timestamps();
    
    // Indice composito per query complesse
    $table->index(['category', 'status']);
    $table->index(['status', 'quantity']);
    
    // Full-text search index
    $table->fullText(['name', 'description']);
});

// Query ottimizzate nel Controller
public function manageItems(Request $request)
{
    $query = Item::select(['id', 'name', 'description', 'category', 'quantity', 'unit_price', 'status'])
                 ->when($request->search, function ($q, $search) {
                     return $q->whereFullText(['name', 'description'], $search);
                 })
                 ->when($request->category, function ($q, $category) {
                     return $q->where('category', $category);
                 })
                 ->when($request->status, function ($q, $status) {
                     return $q->where('status', $status);
                 });
    
    $items = $query->orderBy('name')
                   ->paginate(15)
                   ->withQueryString();
    
    return Inertia::render('Warehouse/ManageItems', [
        'items' => $items,
        'categories' => Cache::remember('item_categories', 3600, function () {
            return Item::distinct()->pluck('category');
        })
    ]);
}
```

### Frontend Optimization

```javascript
// Debouncing per ricerca
const useDebounce = (callback, delay) => {
    let timeoutId
    return (...args) => {
        clearTimeout(timeoutId)
        timeoutId = setTimeout(() => callback(...args), delay)
    }
}

// Lazy loading componenti
const ManageItems = defineAsyncComponent(() => import('./ManageItems.vue'))
const CreateItem = defineAsyncComponent(() => import('./CreateItem.vue'))

// Memoizzazione computed costosi
const expensiveComputation = computed(() => {
    // Calcoli complessi qui
    return complexCalculation(props.items)
})

// Virtual scrolling per liste lunghe (se necessario)
import { VirtualList } from '@tanstack/vue-virtual'
```

### Caching Strategy

```php
// AppServiceProvider
public function boot()
{
    // Cache query frequenti
    if (app()->environment('production')) {
        DB::listen(function ($query) {
            if ($query->time > 1000) {  // Query > 1 secondo
                Log::warning("Slow query detected: {$query->sql}", [
                    'time' => $query->time,
                    'bindings' => $query->bindings
                ]);
            }
        });
    }
}

// Controller con cache
public function dashboard()
{
    $stats = Cache::remember('warehouse_stats', 300, function () {  // 5 minuti
        return [
            'total_items' => Item::count(),
            'low_stock_items' => Item::where('status', 'low_stock')->count(),
            'out_of_stock_items' => Item::where('status', 'out_of_stock')->count(),
            'total_value' => Item::sum(DB::raw('quantity * unit_price'))
        ];
    });
    
    return Inertia::render('Warehouse/Index', compact('stats'));
}
```

---

## 🧪 **Testing & Debugging**

### Struttura Test

```php
// tests/Feature/WarehouseTest.php
class WarehouseTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_admin_can_view_warehouse_dashboard()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        
        $response = $this->actingAs($admin)
                         ->get('/warehouse');
        
        $response->assertStatus(200)
                 ->assertInertia(fn (Assert $page) => 
                     $page->component('Warehouse/Index')
                          ->has('stats')
                 );
    }
    
    public function test_user_cannot_access_warehouse()
    {
        $user = User::factory()->create(['role' => 'user']);
        
        $response = $this->actingAs($user)
                         ->get('/warehouse');
        
        $response->assertStatus(403);
    }
    
    public function test_admin_can_create_item()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $itemData = [
            'name' => 'Test Item',
            'description' => 'Test Description',
            'category' => 'Elettronica',
            'quantity' => 10,
            'unit_price' => 99.99,
            'location' => 'A1-B2',
            'min_quantity' => 5
        ];
        
        $response = $this->actingAs($admin)
                         ->post('/warehouse', $itemData);
        
        $response->assertRedirect('/warehouse/manage')
                 ->assertSessionHas('success');
        
        $this->assertDatabaseHas('items', [
            'name' => 'Test Item',
            'status' => 'available'
        ]);
    }
    
    public function test_item_status_calculated_correctly()
    {
        $item = Item::factory()->create([
            'quantity' => 3,
            'min_quantity' => 5
        ]);
        
        $this->assertEquals('low_stock', $item->fresh()->status);
        
        $item->update(['quantity' => 0]);
        $this->assertEquals('out_of_stock', $item->fresh()->status);
        
        $item->update(['quantity' => 10]);
        $this->assertEquals('available', $item->fresh()->status);
    }
}
```

### Debugging Tools

```php
// Logging personalizzato
Log::channel('warehouse')->info('Item created', [
    'item_id' => $item->id,
    'user_id' => auth()->id(),
    'action' => 'create'
]);

// Query debugging
DB::enableQueryLog();
// ... query operations
dd(DB::getQueryLog());

// Dump delle request Inertia
// In controller
dump($request->all());
return Inertia::render('Component', $data);
```

### Environment Configuration

```env
# .env.local (development)
APP_ENV=local
APP_DEBUG=true
LOG_LEVEL=debug

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=code_challenge
DB_USERNAME=root
DB_PASSWORD=

# Debugging
TELESCOPE_ENABLED=true
DEBUGBAR_ENABLED=true
```

---

## 🔧 **Maintenance & Deployment**

### Comandi Maintenance

```bash
# Clear all caches
php artisan optimize:clear

# Optimize for production
php artisan optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Database maintenance
php artisan migrate --force
php artisan db:seed --force

# Storage linking
php artisan storage:link

# Queue workers (se necessario)
php artisan queue:work --daemon
```

### Production Checklist

```bash
# ✅ Environment
- [ ] APP_ENV=production
- [ ] APP_DEBUG=false
- [ ] APP_KEY generated
- [ ] Database configured
- [ ] Mail configured
- [ ] Cache driver configured

# ✅ Security
- [ ] HTTPS enabled
- [ ] Security headers
- [ ] Rate limiting
- [ ] Input validation
- [ ] SQL injection protection
- [ ] XSS protection

# ✅ Performance
- [ ] Opcache enabled
- [ ] Config cached
- [ ] Routes cached
- [ ] Views cached
- [ ] Database indexed
- [ ] CDN configured

# ✅ Monitoring
- [ ] Error logging
- [ ] Performance monitoring
- [ ] Uptime monitoring
- [ ] Database monitoring
```

---

**Documentazione tecnica completata** ✅  
*Sistema Code Challenge pronto per sviluppo e produzione*
