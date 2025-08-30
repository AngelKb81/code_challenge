<template>
    <AppLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                        Nuova Richiesta
                    </h2>
                    <p class="text-gray-600 text-sm mt-1">
                        Richiedi l'uso di un articolo del magazzino
                    </p>
                </div>
                <div class="flex space-x-2">
                    <Link 
                        :href="route('warehouse.requests')" 
                        class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition duration-200"
                    >
                        ← Le Mie Richieste
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-8">
                        <form @submit.prevent="submit">
                            <!-- Selezione Tipo Richiesta -->
                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-3">
                                    Tipo di Richiesta *
                                </label>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div 
                                        @click="form.request_type = 'existing_item'"
                                        :class="[
                                            'border-2 rounded-lg p-4 cursor-pointer transition-all',
                                            form.request_type === 'existing_item' 
                                                ? 'border-blue-500 bg-blue-50' 
                                                : 'border-gray-300 hover:border-gray-400'
                                        ]"
                                    >
                                        <div class="flex items-center">
                                            <input 
                                                type="radio" 
                                                value="existing_item" 
                                                v-model="form.request_type"
                                                class="mr-3"
                                            >
                                            <div>
                                                <h3 class="font-semibold text-gray-900">Richiesta Item Esistente</h3>
                                                <p class="text-sm text-gray-600">Richiedi l'uso temporaneo di un articolo già in magazzino</p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div 
                                        @click="form.request_type = 'purchase_request'"
                                        :class="[
                                            'border-2 rounded-lg p-4 cursor-pointer transition-all',
                                            form.request_type === 'purchase_request' 
                                                ? 'border-green-500 bg-green-50' 
                                                : 'border-gray-300 hover:border-gray-400'
                                        ]"
                                    >
                                        <div class="flex items-center">
                                            <input 
                                                type="radio" 
                                                value="purchase_request" 
                                                v-model="form.request_type"
                                                class="mr-3"
                                            >
                                            <div>
                                                <h3 class="font-semibold text-gray-900">Richiesta Acquisto</h3>
                                                <p class="text-sm text-gray-600">Richiedi l'acquisto di un nuovo articolo non presente in magazzino</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div v-if="form.errors.request_type" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.request_type }}
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Selezione Articolo (solo per existing_item) -->
                                <div v-if="form.request_type === 'existing_item'" class="md:col-span-2">
                                    <label for="item_id" class="block text-sm font-medium text-gray-700 mb-2">
                                        Articolo Richiesto *
                                    </label>
                                    <select
                                        id="item_id"
                                        v-model="form.item_id"
                                        @change="onItemSelected"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                        required
                                    >
                                        <option value="">Seleziona un articolo...</option>
                                        <option 
                                            v-for="item in availableItems" 
                                            :key="item.id" 
                                            :value="item.id"
                                            :disabled="(item.available_quantity || 0) === 0"
                                        >
                                            {{ item.name }} - {{ item.category }} 
                                            ({{ item.available_quantity || 0 }} disponibili di {{ item.quantity }})
                                        </option>
                                    </select>
                                    <div v-if="form.errors.item_id" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.item_id }}
                                    </div>
                                </div>

                                <!-- Dettagli Articolo Selezionato -->
                                <div v-if="selectedItem" class="md:col-span-2 bg-blue-50 p-4 rounded-lg">
                                    <h4 class="font-medium text-blue-900 mb-2">Dettagli Articolo</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                                        <div>
                                            <span class="font-medium text-blue-700">Nome:</span>
                                            <p class="text-blue-900">{{ selectedItem.name }}</p>
                                        </div>
                                        <div>
                                            <span class="font-medium text-blue-700">Categoria:</span>
                                            <p class="text-blue-900">{{ selectedItem.category }}</p>
                                        </div>
                                        <div>
                                            <span class="font-medium text-blue-700">Disponibili:</span>
                                            <p class="text-blue-900">{{ selectedItem.available_quantity || 0 }} di {{ selectedItem.quantity }}</p>
                                        </div>
                                        <div v-if="selectedItem.description" class="md:col-span-3">
                                            <span class="font-medium text-blue-700">Descrizione:</span>
                                            <p class="text-blue-900">{{ selectedItem.description }}</p>
                                        </div>
                                        <div v-if="selectedItem.location" class="md:col-span-3">
                                            <span class="font-medium text-blue-700">Posizione:</span>
                                            <p class="text-blue-900">{{ selectedItem.location }}</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Campi per Purchase Request -->
                                <div v-if="form.request_type === 'purchase_request'" class="md:col-span-2 space-y-4">
                                    <h4 class="font-medium text-green-900 border-b border-green-200 pb-2">Dettagli Item da Acquistare</h4>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <!-- Nome Item -->
                                        <div>
                                            <label for="item_name" class="block text-sm font-medium text-gray-700 mb-2">
                                                Nome Articolo *
                                            </label>
                                            <input
                                                id="item_name"
                                                type="text"
                                                v-model="form.item_name"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                                placeholder="Es. Monitor 4K Dell"
                                                required
                                            >
                                            <div v-if="form.errors.item_name" class="mt-1 text-sm text-red-600">
                                                {{ form.errors.item_name }}
                                            </div>
                                        </div>

                                        <!-- Categoria -->
                                        <div>
                                            <label for="item_category" class="block text-sm font-medium text-gray-700 mb-2">
                                                Categoria *
                                            </label>
                                            <select
                                                id="item_category"
                                                v-model="form.item_category"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                                required
                                            >
                                                <option value="">Seleziona categoria...</option>
                                                <option v-for="category in categories" :key="category" :value="category">
                                                    {{ category }}
                                                </option>
                                                <option value="Altra">Altra categoria</option>
                                            </select>
                                            <div v-if="form.errors.item_category" class="mt-1 text-sm text-red-600">
                                                {{ form.errors.item_category }}
                                            </div>
                                        </div>

                                        <!-- Brand -->
                                        <div>
                                            <label for="item_brand" class="block text-sm font-medium text-gray-700 mb-2">
                                                Brand (opzionale)
                                            </label>
                                            <input
                                                id="item_brand"
                                                type="text"
                                                v-model="form.item_brand"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                                placeholder="Es. Dell, Apple, HP"
                                            >
                                            <div v-if="form.errors.item_brand" class="mt-1 text-sm text-red-600">
                                                {{ form.errors.item_brand }}
                                            </div>
                                        </div>

                                        <!-- Costo Stimato -->
                                        <div>
                                            <label for="estimated_cost" class="block text-sm font-medium text-gray-700 mb-2">
                                                Costo Stimato (€) *
                                            </label>
                                            <input
                                                id="estimated_cost"
                                                type="number"
                                                step="0.01"
                                                min="0"
                                                v-model="form.estimated_cost"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                                placeholder="0.00"
                                                required
                                            >
                                            <div v-if="form.errors.estimated_cost" class="mt-1 text-sm text-red-600">
                                                {{ form.errors.estimated_cost }}
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Descrizione Item -->
                                    <div>
                                        <label for="item_description" class="block text-sm font-medium text-gray-700 mb-2">
                                            Descrizione Articolo *
                                        </label>
                                        <textarea
                                            id="item_description"
                                            v-model="form.item_description"
                                            rows="3"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                            placeholder="Descrivi dettagliatamente l'articolo richiesto, specifiche tecniche, dimensioni, etc."
                                            required
                                        ></textarea>
                                        <div v-if="form.errors.item_description" class="mt-1 text-sm text-red-600">
                                            {{ form.errors.item_description }}
                                        </div>
                                    </div>

                                    <!-- Giustificazione -->
                                    <div>
                                        <label for="justification" class="block text-sm font-medium text-gray-700 mb-2">
                                            Giustificazione Acquisto *
                                        </label>
                                        <textarea
                                            id="justification"
                                            v-model="form.justification"
                                            rows="3"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                            placeholder="Spiega perché è necessario acquistare questo articolo e come verrà utilizzato"
                                            required
                                        ></textarea>
                                        <div v-if="form.errors.justification" class="mt-1 text-sm text-red-600">
                                            {{ form.errors.justification }}
                                        </div>
                                    </div>

                                    <!-- Info Fornitore -->
                                    <div>
                                        <label for="supplier_info" class="block text-sm font-medium text-gray-700 mb-2">
                                            Info Fornitore (opzionale)
                                        </label>
                                        <textarea
                                            id="supplier_info"
                                            v-model="form.supplier_info"
                                            rows="2"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                            placeholder="Suggerimenti su fornitori o link a prodotti specifici"
                                        ></textarea>
                                        <div v-if="form.errors.supplier_info" class="mt-1 text-sm text-red-600">
                                            {{ form.errors.supplier_info }}
                                        </div>
                                    </div>
                                </div>

                                <!-- Date (solo per existing items) -->
                                <div v-if="form.request_type === 'existing_item'" class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <!-- Data Inizio -->
                                    <div>
                                        <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">
                                            Data Inizio *
                                        </label>
                                        <input
                                            id="start_date"
                                            type="date"
                                            v-model="form.start_date"
                                            :min="today"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                            required
                                        >
                                        <div v-if="form.errors.start_date" class="mt-1 text-sm text-red-600">
                                            {{ form.errors.start_date }}
                                        </div>
                                    </div>

                                    <!-- Data Fine -->
                                    <div>
                                        <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">
                                            Data Fine *
                                        </label>
                                        <input
                                            id="end_date"
                                            type="date"
                                            v-model="form.end_date"
                                            :min="form.start_date || today"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                            required
                                        >
                                        <div v-if="form.errors.end_date" class="mt-1 text-sm text-red-600">
                                            {{ form.errors.end_date }}
                                        </div>
                                    </div>
                                </div>

                                <!-- Priorità -->
                                <div>
                                    <label for="priority" class="block text-sm font-medium text-gray-700 mb-2">
                                        Priorità *
                                    </label>
                                    <select
                                        id="priority"
                                        v-model="form.priority"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                        required
                                    >
                                        <option value="low">Bassa</option>
                                        <option value="medium" selected>Media</option>
                                        <option value="high">Alta</option>
                                        <option value="urgent">Urgente</option>
                                    </select>
                                    <div v-if="form.errors.priority" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.priority }}
                                    </div>
                                </div>

                                <!-- Quantità Richiesta - Existing Item -->
                                <div v-if="form.request_type === 'existing_item'">
                                    <label for="quantity_requested" class="block text-sm font-medium text-gray-700 mb-2">
                                        Quantità Richiesta
                                    </label>
                                    <input
                                        id="quantity_requested"
                                        type="number"
                                        v-model="form.quantity_requested"
                                        min="1"
                                        :max="selectedItem?.available_quantity || 0"
                                        :disabled="!selectedItem || (selectedItem?.available_quantity || 0) === 0"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 disabled:bg-gray-100 disabled:cursor-not-allowed"
                                    >
                                    <div v-if="selectedItem && (selectedItem?.available_quantity || 0) === 0" class="mt-1 text-sm text-red-600">
                                        Questo articolo non è attualmente disponibile
                                    </div>
                                    <div v-if="form.errors.quantity_requested" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.quantity_requested }}
                                    </div>
                                </div>

                                <!-- Quantità Richiesta - Purchase Request -->
                                <div v-if="form.request_type === 'purchase_request'">
                                    <label for="quantity_requested_purchase" class="block text-sm font-medium text-gray-700 mb-2">
                                        Quantità Richiesta
                                    </label>
                                    <input
                                        id="quantity_requested_purchase"
                                        type="number"
                                        v-model="form.quantity_requested"
                                        min="1"
                                        max="100"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                    >
                                    <div v-if="form.errors.quantity_requested" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.quantity_requested }}
                                    </div>
                                </div>

                                <!-- Motivo -->
                                <div class="md:col-span-2">
                                    <label for="reason" class="block text-sm font-medium text-gray-700 mb-2">
                                        Motivo della Richiesta *
                                    </label>
                                    <textarea
                                        id="reason"
                                        v-model="form.reason"
                                        rows="3"
                                        maxlength="500"
                                        placeholder="Descrivi brevemente il motivo per cui hai bisogno di questo articolo..."
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                        required
                                    ></textarea>
                                    <div class="mt-1 text-xs text-gray-500">
                                        {{ form.reason ? form.reason.length : 0 }}/500 caratteri
                                    </div>
                                    <div v-if="form.errors.reason" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.reason }}
                                    </div>
                                </div>

                                <!-- Note Aggiuntive -->
                                <div class="md:col-span-2">
                                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                                        Note Aggiuntive (opzionale)
                                    </label>
                                    <textarea
                                        id="notes"
                                        v-model="form.notes"
                                        rows="2"
                                        maxlength="1000"
                                        placeholder="Eventuali informazioni aggiuntive o specifiche..."
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                    ></textarea>
                                    <div class="mt-1 text-xs text-gray-500">
                                        {{ form.notes ? form.notes.length : 0 }}/1000 caratteri
                                    </div>
                                    <div v-if="form.errors.notes" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.notes }}
                                    </div>
                                </div>
                            </div>

                            <!-- Riepilogo Durata -->
                            <div v-if="(form.request_type === 'existing_item' && form.start_date && form.end_date) || (form.request_type === 'purchase_request' && (form.item_name || form.reason))" class="mt-6 bg-gray-50 p-4 rounded-lg">
                                <h4 class="font-medium text-gray-900 mb-2">Riepilogo Richiesta</h4>
                                <div class="text-sm text-gray-700">
                                    <p v-if="form.request_type === 'existing_item' && form.start_date && form.end_date">
                                        <strong>Durata:</strong> {{ calculateDuration() }} giorni
                                    </p>
                                    <p v-if="form.request_type === 'existing_item' && form.start_date && form.end_date">
                                        <strong>Dal:</strong> {{ formatDate(form.start_date) }} <strong>al:</strong> {{ formatDate(form.end_date) }}
                                    </p>
                                    <p v-if="form.request_type === 'existing_item' && selectedItem">
                                        <strong>Articolo:</strong> {{ selectedItem.name }}
                                    </p>
                                    <p v-if="form.request_type === 'purchase_request' && form.item_name">
                                        <strong>Articolo:</strong> {{ form.item_name }}
                                    </p>
                                    <p v-if="form.request_type === 'purchase_request' && form.estimated_cost">
                                        <strong>Costo Stimato:</strong> €{{ form.estimated_cost }}
                                    </p>
                                    <p><strong>Priorità:</strong> {{ getPriorityLabel(form.priority) }}</p>
                                </div>
                            </div>

                            <!-- Pulsanti -->
                            <div class="mt-8 flex justify-end space-x-3">
                                <Link
                                    :href="route('warehouse.requests')"
                                    class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition duration-200"
                                >
                                    Annulla
                                </Link>
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    <span v-if="form.processing">Invio in corso...</span>
                                    <span v-else>Invia Richiesta</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Info Box -->
                <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-800">
                                Informazioni sulla Richiesta
                            </h3>
                            <div class="mt-2 text-sm text-blue-700">
                                <ul class="list-disc list-inside space-y-1">
                                    <li>La richiesta sarà sottoposta all'approvazione di un amministratore</li>
                                    <li>Riceverai una notifica via email quando la richiesta viene approvata o rifiutata</li>
                                    <li>Assicurati di restituire l'articolo entro la data concordata</li>
                                    <li>Per richieste urgenti, contatta direttamente un amministratore</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { Link } from '@inertiajs/vue3'

const props = defineProps({
    availableItems: Array,
    categories: Array,
})

// Form data
const form = useForm({
    request_type: 'existing_item',
    item_id: '',
    // Campi per purchase request
    item_name: '',
    item_description: '',
    item_category: '',
    item_brand: '',
    estimated_cost: '',
    supplier_info: '',
    justification: '',
    // Campi comuni
    start_date: '',
    end_date: '',
    reason: '',
    notes: '',
    priority: 'medium',
    quantity_requested: 1,
})

// State
const selectedItem = ref(null)

// Computed
const today = computed(() => {
    return new Date().toISOString().split('T')[0]
})

const isExistingItemRequest = computed(() => {
    return form.request_type === 'existing_item'
})

const isPurchaseRequest = computed(() => {
    return form.request_type === 'purchase_request'
})

// Methods
const onItemSelected = () => {
    selectedItem.value = props.availableItems.find(item => item.id == form.item_id) || null
    if (selectedItem.value) {
        form.quantity_requested = 1
    }
}

const submit = () => {
    // Reset campi non necessari in base al tipo di richiesta
    if (form.request_type === 'existing_item') {
        // Validazione per item esistente
        if (!selectedItem.value) {
            alert('Seleziona un articolo prima di procedere');
            return;
        }
        
        if ((selectedItem.value.available_quantity || 0) === 0) {
            alert('L\'articolo selezionato non è attualmente disponibile');
            return;
        }
        
        if (form.quantity_requested > (selectedItem.value.available_quantity || 0)) {
            alert(`Quantità non disponibile. Massimo ${selectedItem.value.available_quantity || 0} unità`);
            return;
        }
        
        // Reset campi per purchase request
        form.item_name = '';
        form.item_description = '';
        form.item_category = '';
        form.item_brand = '';
        form.estimated_cost = '';
        form.supplier_info = '';
        form.justification = '';
    } else {
        // Validazione per purchase request
        if (!form.item_name.trim()) {
            alert('Il nome dell\'articolo è obbligatorio per le richieste di acquisto');
            return;
        }
        
        if (!form.item_description.trim()) {
            alert('La descrizione dell\'articolo è obbligatoria per le richieste di acquisto');
            return;
        }
        
        if (!form.item_category.trim()) {
            alert('La categoria è obbligatoria per le richieste di acquisto');
            return;
        }
        
        if (!form.justification.trim()) {
            alert('La giustificazione è obbligatoria per le richieste di acquisto');
            return;
        }
        
        // Reset campo per existing item
        form.item_id = '';
        
        // Reset date per purchase request (non necessarie)
        form.start_date = '';
        form.end_date = '';
    }
    
    form.post(route('warehouse.requests.store'), {
        onSuccess: () => {
            form.reset();
        }
    });
}

const calculateDuration = () => {
    if (!form.start_date || !form.end_date) return 0
    const start = new Date(form.start_date)
    const end = new Date(form.end_date)
    const diffTime = Math.abs(end - start)
    return Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1 // +1 to include both start and end days
}

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('it-IT', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    })
}

const getPriorityLabel = (priority) => {
    const labels = {
        low: 'Bassa',
        medium: 'Media',
        high: 'Alta',
        urgent: 'Urgente'
    }
    return labels[priority] || priority
}

// Watch for date changes to auto-set minimum end date
watch(() => form.start_date, (newDate) => {
    if (newDate && form.end_date && form.end_date < newDate) {
        form.end_date = newDate
    }
})
</script>
