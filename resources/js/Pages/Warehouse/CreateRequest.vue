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
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Selezione Articolo -->
                                <div class="md:col-span-2">
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

                                <!-- Quantità Richiesta -->
                                <div>
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
                            <div v-if="form.start_date && form.end_date" class="mt-6 bg-gray-50 p-4 rounded-lg">
                                <h4 class="font-medium text-gray-900 mb-2">Riepilogo Richiesta</h4>
                                <div class="text-sm text-gray-700">
                                    <p><strong>Durata:</strong> {{ calculateDuration() }} giorni</p>
                                    <p><strong>Dal:</strong> {{ formatDate(form.start_date) }}</p>
                                    <p><strong>Al:</strong> {{ formatDate(form.end_date) }}</p>
                                    <p v-if="selectedItem"><strong>Articolo:</strong> {{ selectedItem.name }}</p>
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
})

// Form data
const form = useForm({
    item_id: '',
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

// Methods
const onItemSelected = () => {
    selectedItem.value = props.availableItems.find(item => item.id == form.item_id) || null
    if (selectedItem.value) {
        form.quantity_requested = 1
    }
}

const submit = () => {
    // Validazione frontend aggiuntiva
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
    
    form.post(route('warehouse.requests.store'), {
        onSuccess: () => {
            // Redirect will be handled by Laravel
        }
    })
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
