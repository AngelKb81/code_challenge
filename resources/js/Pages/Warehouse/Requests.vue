<template>
    <AppLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                        {{ $page.props.auth.user.role === 'admin' ? 'Gestisci Richieste' : 'Le Mie Richieste' }}
                    </h2>
                    <p class="text-gray-600 text-sm mt-1">
                        {{ $page.props.auth.user.role === 'admin' ? 'Approva, rifiuta e gestisci tutte le richieste' : 'Visualizza e gestisci le tue richieste' }}
                    </p>
                </div>
                <div class="flex space-x-2">
                    <Link 
                        :href="route('warehouse.index')" 
                        class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition duration-200"
                    >
                        ← Dashboard
                    </Link>
                    <Link 
                        :href="route('warehouse.requests.create')" 
                        class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition duration-200"
                    >
                        + Nuova Richiesta
                    </Link>
                </div>
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
                                    Stato
                                </label>
                                <select v-model="selectedStatus" @change="applyFilters" class="w-full border-gray-300 rounded-md">
                                    <option value="">Tutti gli stati</option>
                                    <option v-for="status in statuses" :key="status" :value="status">
                                        {{ getStatusLabel(status) }}
                                    </option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Priorità
                                </label>
                                <select v-model="selectedPriority" @change="applyFilters" class="w-full border-gray-300 rounded-md">
                                    <option value="">Tutte le priorità</option>
                                    <option v-for="priority in priorities" :key="priority" :value="priority">
                                        {{ getPriorityLabel(priority) }}
                                    </option>
                                </select>
                            </div>
                            
                            <div v-if="$page.props.auth.user.role === 'admin'">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Utente
                                </label>
                                <select v-model="selectedUser" @change="applyFilters" class="w-full border-gray-300 rounded-md">
                                    <option value="">Tutti gli utenti</option>
                                    <option v-for="user in users" :key="user.id" :value="user.id">
                                        {{ user.name }}
                                    </option>
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

                <!-- Lista Richieste -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Richiesta
                                    </th>
                                    <th v-if="$page.props.auth.user.role === 'admin'" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Utente
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Periodo
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Priorità
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Stato
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Data Richiesta
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Azioni
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="request in requests.data" :key="request.id" class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">
                                                <span v-if="request.request_type === 'existing_item' && request.item">
                                                    {{ request.item.name }}
                                                </span>
                                                <span v-else-if="request.request_type === 'purchase_request'">
                                                    {{ request.item_name }}
                                                </span>
                                                <span v-else class="text-gray-400 italic">
                                                    Nome non disponibile
                                                </span>
                                            </div>
                                            <div class="text-xs text-blue-600 font-medium">
                                                <span v-if="request.request_type === 'existing_item'">
                                                    📦 Articolo Esistente
                                                </span>
                                                <span v-else-if="request.request_type === 'purchase_request'">
                                                    🛒 Richiesta Acquisto
                                                </span>
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                {{ request.reason }}
                                            </div>
                                            <div v-if="request.quantity_requested > 1" class="text-xs text-gray-500">
                                                Quantità: {{ request.quantity_requested }}
                                            </div>
                                        </div>
                                    </td>
                                    <td v-if="$page.props.auth.user.role === 'admin'" class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ request.user.name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <div>
                                            <div>{{ formatDate(request.start_date) }}</div>
                                            <div class="text-xs text-gray-500">fino al {{ formatDate(request.end_date) }}</div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="{
                                            'bg-gray-100 text-gray-800': request.priority === 'low',
                                            'bg-blue-100 text-blue-800': request.priority === 'medium',
                                            'bg-yellow-100 text-yellow-800': request.priority === 'high',
                                            'bg-red-100 text-red-800': request.priority === 'urgent'
                                        }" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                                            {{ getPriorityLabel(request.priority) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="{
                                            'bg-yellow-100 text-yellow-800': request.status === 'pending',
                                            'bg-green-100 text-green-800': request.status === 'approved',
                                            'bg-red-100 text-red-800': request.status === 'rejected',
                                            'bg-blue-100 text-blue-800': request.status === 'in_use',
                                            'bg-gray-100 text-gray-800': request.status === 'returned',
                                            'bg-orange-100 text-orange-800': request.status === 'overdue'
                                        }" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                                            {{ getStatusLabel(request.status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ formatDate(request.created_at) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex flex-wrap gap-2">
                                            <!-- Azioni Admin -->
                                            <template v-if="$page.props.auth.user.role === 'admin'">
                                                <button
                                                    v-if="request.status === 'pending'"
                                                    @click="approveRequest(request)"
                                                    class="inline-flex items-center px-3 py-1 text-xs font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500"
                                                >
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                    </svg>
                                                    Approva
                                                </button>
                                                <button
                                                    v-if="request.status === 'pending'"
                                                    @click="showRejectModal(request)"
                                                    class="inline-flex items-center px-3 py-1 text-xs font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500"
                                                >
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                    </svg>
                                                    Rifiuta
                                                </button>
                                                <button
                                                    v-if="request.status === 'approved' || request.status === 'in_use'"
                                                    @click="markAsReturned(request)"
                                                    class="inline-flex items-center px-3 py-1 text-xs font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                >
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                                    </svg>
                                                    Restituito
                                                </button>
                                            </template>
                                            
                                            <!-- Visualizza Dettagli -->
                                            <button
                                                @click="showDetails(request)"
                                                class="inline-flex items-center px-3 py-1 text-xs font-medium rounded-md text-indigo-600 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                            >
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                </svg>
                                                Dettagli
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginazione -->
                    <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                        <div class="flex items-center justify-between">
                            <div class="flex justify-between flex-1 sm:hidden">
                                <Link
                                    v-if="requests.prev_page_url"
                                    :href="requests.prev_page_url"
                                    class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                                >
                                    Precedente
                                </Link>
                                <Link
                                    v-if="requests.next_page_url"
                                    :href="requests.next_page_url"
                                    class="relative ml-3 inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                                >
                                    Successivo
                                </Link>
                            </div>
                            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                                <div>
                                    <p class="text-sm text-gray-700">
                                        Mostrando da {{ requests.from }} a {{ requests.to }} di {{ requests.total }} risultati
                                    </p>
                                </div>
                                <div>
                                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                                        <Link
                                            v-for="link in requests.links"
                                            :key="link.label"
                                            :href="link.url"
                                            :class="{
                                                'bg-blue-50 border-blue-500 text-blue-600': link.active,
                                                'bg-white border-gray-300 text-gray-500 hover:bg-gray-50': !link.active,
                                                'cursor-not-allowed opacity-50': !link.url
                                            }"
                                            class="relative inline-flex items-center px-4 py-2 border text-sm font-medium"
                                            v-html="link.label"
                                        />
                                    </nav>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Messaggio se nessuna richiesta -->
                    <div v-if="requests.data.length === 0" class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">Nessuna richiesta</h3>
                        <p class="mt-1 text-sm text-gray-500">
                            {{ hasFilters ? 'Nessuna richiesta trovata con i filtri attuali.' : 'Non hai ancora fatto richieste.' }}
                        </p>
                        <div class="mt-6">
                            <Link
                                :href="route('warehouse.requests.create')"
                                class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700"
                            >
                                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                                Nuova Richiesta
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Rifiuta Richiesta -->
        <Modal :show="showingRejectModal" @close="showingRejectModal = false">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">
                    Rifiuta Richiesta
                </h3>
                <p class="text-sm text-gray-600 mb-4">
                    Stai per rifiutare la richiesta per "{{ selectedRequest?.item?.name }}". 
                    Inserisci il motivo del rifiuto:
                </p>
                <form @submit.prevent="rejectRequest">
                    <textarea
                        v-model="rejectReason"
                        rows="3"
                        class="w-full border-gray-300 rounded-md"
                        placeholder="Motivo del rifiuto..."
                        required
                    ></textarea>
                    <div class="mt-4 flex justify-end space-x-2">
                        <button
                            type="button"
                            @click="showingRejectModal = false"
                            class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
                        >
                            Annulla
                        </button>
                        <button
                            type="submit"
                            class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700"
                        >
                            Rifiuta Richiesta
                        </button>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Modal Dettagli Richiesta -->
        <Modal :show="showingDetailsModal" @close="showingDetailsModal = false">
            <div class="p-6" v-if="selectedRequest">
                <h3 class="text-lg font-medium text-gray-900 mb-4">
                    Dettagli Richiesta
                </h3>
                <div class="space-y-3">
                    <div>
                        <label class="text-sm font-medium text-gray-700">Tipo Richiesta:</label>
                        <p class="text-sm text-gray-900">
                            <span v-if="selectedRequest.request_type === 'existing_item'" class="inline-flex items-center text-blue-600">
                                📦 Articolo Esistente
                            </span>
                            <span v-else-if="selectedRequest.request_type === 'purchase_request'" class="inline-flex items-center text-green-600">
                                🛒 Richiesta Acquisto
                            </span>
                        </p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700">Articolo:</label>
                        <p class="text-sm text-gray-900">
                            <span v-if="selectedRequest.request_type === 'existing_item' && selectedRequest.item">
                                {{ selectedRequest.item.name }}
                            </span>
                            <span v-else-if="selectedRequest.request_type === 'purchase_request'">
                                {{ selectedRequest.item_name }}
                            </span>
                        </p>
                        <div v-if="selectedRequest.request_type === 'purchase_request'" class="mt-2 text-sm text-gray-600">
                            <p v-if="selectedRequest.item_description"><strong>Descrizione:</strong> {{ selectedRequest.item_description }}</p>
                            <p v-if="selectedRequest.item_category"><strong>Categoria:</strong> {{ selectedRequest.item_category }}</p>
                            <p v-if="selectedRequest.item_brand"><strong>Brand:</strong> {{ selectedRequest.item_brand }}</p>
                            <p v-if="selectedRequest.estimated_cost"><strong>Costo Stimato:</strong> €{{ selectedRequest.estimated_cost }}</p>
                            <p v-if="selectedRequest.supplier_info"><strong>Fornitore:</strong> {{ selectedRequest.supplier_info }}</p>
                        </div>
                    </div>
                    <div v-if="$page.props.auth.user.role === 'admin'">
                        <label class="text-sm font-medium text-gray-700">Richiesto da:</label>
                        <p class="text-sm text-gray-900">{{ selectedRequest.user.name }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700">Periodo:</label>
                        <p class="text-sm text-gray-900">
                            Dal {{ formatDate(selectedRequest.start_date) }} al {{ formatDate(selectedRequest.end_date) }}
                        </p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700">Motivo:</label>
                        <p class="text-sm text-gray-900">{{ selectedRequest.reason }}</p>
                    </div>
                    <div v-if="selectedRequest.request_type === 'purchase_request' && selectedRequest.justification">
                        <label class="text-sm font-medium text-gray-700">Giustificazione Acquisto:</label>
                        <p class="text-sm text-gray-900">{{ selectedRequest.justification }}</p>
                    </div>
                    <div v-if="selectedRequest.notes">
                        <label class="text-sm font-medium text-gray-700">Note:</label>
                        <p class="text-sm text-gray-900">{{ selectedRequest.notes }}</p>
                    </div>
                    <div v-if="selectedRequest.rejection_reason">
                        <label class="text-sm font-medium text-gray-700">Motivo rifiuto:</label>
                        <p class="text-sm text-red-600">{{ selectedRequest.rejection_reason }}</p>
                    </div>
                    <div v-if="selectedRequest.approver">
                        <label class="text-sm font-medium text-gray-700">{{ selectedRequest.status === 'approved' ? 'Approvato' : 'Gestito' }} da:</label>
                        <p class="text-sm text-gray-900">{{ selectedRequest.approver.name }}</p>
                    </div>
                </div>
                <div class="mt-6 flex justify-end">
                    <button
                        @click="showingDetailsModal = false"
                        class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700"
                    >
                        Chiudi
                    </button>
                </div>
            </div>
        </Modal>
    </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { Link } from '@inertiajs/vue3'
import Modal from '@/Components/Modal.vue'

const props = defineProps({
    requests: Object,
    statuses: Array,
    priorities: Array,
    users: Array,
    filters: Object,
})

// State
const selectedStatus = ref(props.filters.status || '')
const selectedPriority = ref(props.filters.priority || '')
const selectedUser = ref(props.filters.user || '')
const showingRejectModal = ref(false)
const showingDetailsModal = ref(false)
const selectedRequest = ref(null)
const rejectReason = ref('')

// Computed
const hasFilters = computed(() => {
    return selectedStatus.value || selectedPriority.value || selectedUser.value
})

// Methods
const applyFilters = () => {
    router.get(route('warehouse.requests'), {
        status: selectedStatus.value,
        priority: selectedPriority.value,
        user: selectedUser.value
    }, {
        preserveState: true,
        preserveScroll: true
    })
}

const clearFilters = () => {
    selectedStatus.value = ''
    selectedPriority.value = ''
    selectedUser.value = ''
    applyFilters()
}

const approveRequest = (request) => {
    if (confirm('Confermi di voler approvare questa richiesta?')) {
        router.patch(route('warehouse.requests.approve', request.id), {}, {
            onSuccess: () => {
                // La pagina si ricaricherà automaticamente
            },
            onError: (errors) => {
                alert('Errore durante l\'approvazione: ' + Object.values(errors).join(', '))
            }
        })
    }
}

const showRejectModal = (request) => {
    selectedRequest.value = request
    rejectReason.value = ''
    showingRejectModal.value = true
}

const rejectRequest = () => {
    if (!rejectReason.value.trim()) {
        alert('Inserisci un motivo per il rifiuto')
        return
    }
    
    router.patch(route('warehouse.requests.reject', selectedRequest.value.id), {
        rejection_reason: rejectReason.value
    }, {
        onSuccess: () => {
            showingRejectModal.value = false
            selectedRequest.value = null
            rejectReason.value = ''
        },
        onError: (errors) => {
            alert('Errore durante il rifiuto: ' + Object.values(errors).join(', '))
        }
    })
}

const markAsReturned = (request) => {
    if (confirm('Confermi che questo articolo è stato restituito?')) {
        router.patch(route('warehouse.requests.return', request.id), {}, {
            onSuccess: () => {
                // La pagina si ricaricherà automaticamente
            },
            onError: (errors) => {
                alert('Errore durante l\'operazione: ' + Object.values(errors).join(', '))
            }
        })
    }
}

const showDetails = (request) => {
    selectedRequest.value = request
    showingDetailsModal.value = true
}

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('it-IT')
}

const getStatusLabel = (status) => {
    const labels = {
        pending: 'In Attesa',
        approved: 'Approvata',
        rejected: 'Rifiutata',
        in_use: 'In Uso',
        returned: 'Restituita',
        overdue: 'Scaduta'
    }
    return labels[status] || status
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
</script>
