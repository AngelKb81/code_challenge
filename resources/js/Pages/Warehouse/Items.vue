<template>
    <AppLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Inventario Magazzino
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Filters -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <form @submit.prevent="search" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Cerca</label>
                                <input 
                                    v-model="form.search"
                                    type="text" 
                                    placeholder="Nome, brand, categoria..."
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                >
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Categoria</label>
                                <select 
                                    v-model="form.category"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                >
                                    <option value="">Tutte le categorie</option>
                                    <option v-for="category in categories" :key="category" :value="category">
                                        {{ category }}
                                    </option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Stato</label>
                                <select 
                                    v-model="form.status"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                >
                                    <option value="">Tutti gli stati</option>
                                    <option value="available">Disponibile</option>
                                    <option value="not_available">Non disponibile</option>
                                    <option value="maintenance">In manutenzione</option>
                                    <option value="reserved">Riservato</option>
                                </select>
                            </div>
                            
                            <div class="flex items-end space-x-2">
                                <button 
                                    type="submit"
                                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                                >
                                    Filtra
                                </button>
                                <button 
                                    type="button"
                                    @click="resetFilters"
                                    class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2"
                                >
                                    Reset
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Items Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    <div 
                        v-for="item in items.data" 
                        :key="item.id"
                        class="bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition-shadow"
                    >
                        <div class="p-6">
                            <!-- Status Badge -->
                            <div class="flex justify-between items-start mb-3">
                                <span :class="getStatusClass(item.status)" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                                    {{ getStatusText(item.status) }}
                                </span>
                                <span class="text-xs text-gray-500">
                                    Qty: {{ item.quantity }}
                                </span>
                            </div>

                            <!-- Item Info -->
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ item.name }}</h3>
                            
                            <div class="space-y-1 text-sm text-gray-600 mb-4">
                                <p><span class="font-medium">Brand:</span> {{ item.brand || 'N/A' }}</p>
                                <p><span class="font-medium">Categoria:</span> {{ item.category }}</p>
                                <p><span class="font-medium">Seriale:</span> {{ item.serial_number || 'N/A' }}</p>
                                <p><span class="font-medium">Posizione:</span> {{ item.location || 'N/A' }}</p>
                            </div>

                            <!-- Description -->
                            <p v-if="item.description" class="text-sm text-gray-500 mb-4 line-clamp-2">
                                {{ item.description }}
                            </p>

                            <!-- Purchase Info -->
                            <div v-if="item.purchase_price || item.purchase_date" class="border-t pt-3 text-xs text-gray-500">
                                <p v-if="item.purchase_price">Prezzo: €{{ parseFloat(item.purchase_price).toFixed(2) }}</p>
                                <p v-if="item.purchase_date">Acquisto: {{ formatDate(item.purchase_date) }}</p>
                                <p v-if="item.warranty_expiry">Garanzia fino: {{ formatDate(item.warranty_expiry) }}</p>
                            </div>

                            <!-- Action Button -->
                            <div class="mt-4">
                                <Link 
                                    v-if="item.status === 'available' && item.quantity > 0"
                                    :href="route('warehouse.requests.create', { item_id: item.id })"
                                    class="w-full inline-flex justify-center items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                >
                                    Richiedi
                                </Link>
                                <button 
                                    v-else
                                    disabled
                                    class="w-full inline-flex justify-center items-center px-3 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-400 bg-gray-100 cursor-not-allowed"
                                >
                                    Non disponibile
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="items.links && items.links.length > 3" class="mt-8">
                    <nav class="flex items-center justify-between">
                        <div class="flex-1 flex justify-between sm:hidden">
                            <Link 
                                v-if="items.prev_page_url"
                                :href="items.prev_page_url"
                                class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                            >
                                Precedente
                            </Link>
                            <Link 
                                v-if="items.next_page_url"
                                :href="items.next_page_url"
                                class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                            >
                                Successivo
                            </Link>
                        </div>
                        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm text-gray-700">
                                    Mostrando 
                                    <span class="font-medium">{{ items.from || 0 }}</span>
                                    a
                                    <span class="font-medium">{{ items.to || 0 }}</span>
                                    di
                                    <span class="font-medium">{{ items.total || 0 }}</span>
                                    risultati
                                </p>
                            </div>
                            <div>
                                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                                    <Link 
                                        v-for="link in items.links" 
                                        :key="link.label"
                                        :href="link.url"
                                        v-html="link.label"
                                        :class="[
                                            'relative inline-flex items-center px-2 py-2 border text-sm font-medium',
                                            link.active 
                                                ? 'z-10 bg-blue-50 border-blue-500 text-blue-600' 
                                                : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50',
                                            !link.url ? 'cursor-not-allowed opacity-50' : 'hover:bg-gray-50'
                                        ]"
                                    ></Link>
                                </nav>
                            </div>
                        </div>
                    </nav>
                </div>

                <!-- Empty State -->
                <div v-if="!items.data || items.data.length === 0" class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2M4 13h2m13 0h-1M6 9h12" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Nessun articolo trovato</h3>
                    <p class="mt-1 text-sm text-gray-500">Modifica i filtri di ricerca per trovare altri articoli.</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script>
import AppLayout from '@/Layouts/AppLayout.vue'
import { Link, router } from '@inertiajs/vue3'

export default {
    components: {
        AppLayout,
        Link,
    },

    props: {
        items: Object,
        categories: Array,
        statuses: Array,
        filters: Object,
    },

    data() {
        return {
            form: {
                search: this.filters?.search || '',
                category: this.filters?.category || '',
                status: this.filters?.status || '',
            }
        }
    },

    methods: {
        search() {
            router.get(route('warehouse.items'), this.form, {
                preserveState: true,
                replace: true,
            })
        },

        resetFilters() {
            this.form = {
                search: '',
                category: '',
                status: '',
            }
            this.search()
        },

        formatDate(date) {
            return new Date(date).toLocaleDateString('it-IT')
        },

        getStatusText(status) {
            const statusMap = {
                'available': 'Disponibile',
                'not_available': 'Non disponibile',
                'maintenance': 'In manutenzione',
                'reserved': 'Riservato'
            }
            return statusMap[status] || status
        },

        getStatusClass(status) {
            const classMap = {
                'available': 'bg-green-100 text-green-800',
                'not_available': 'bg-red-100 text-red-800',
                'maintenance': 'bg-yellow-100 text-yellow-800',
                'reserved': 'bg-blue-100 text-blue-800'
            }
            return classMap[status] || 'bg-gray-100 text-gray-800'
        }
    }
}
</script>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
