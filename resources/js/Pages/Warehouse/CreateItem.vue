<template>
    <AppLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Nuovo Articolo Magazzino
                </h2>
                <Link 
                    :href="route('warehouse.items.manage')"
                    class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Torna alla Lista
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <form @submit.prevent="submit" class="p-6 space-y-6">
                        <!-- Basic Information -->
                        <div class="border-b border-gray-200 pb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Informazioni Base</h3>
                            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                <div>
                                    <label for="name" class="block text-sm font-medium text-gray-700">Nome Articolo *</label>
                                    <input
                                        id="name"
                                        v-model="form.name"
                                        type="text"
                                        required
                                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                        :class="{ 'border-red-300': errors.name }"
                                    />
                                    <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name }}</p>
                                </div>

                                <div>
                                    <label for="category" class="block text-sm font-medium text-gray-700">Categoria *</label>
                                    <select
                                        id="category"
                                        v-model="form.category"
                                        required
                                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                        :class="{ 'border-red-300': errors.category }"
                                    >
                                        <option value="">Seleziona categoria</option>
                                        <option v-for="category in categories" :key="category" :value="category">
                                            {{ category }}
                                        </option>
                                        <option value="custom">+ Nuova categoria</option>
                                    </select>
                                    <input
                                        v-if="form.category === 'custom'"
                                        v-model="form.custom_category"
                                        type="text"
                                        placeholder="Inserisci nuova categoria"
                                        class="mt-2 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                    />
                                    <p v-if="errors.category" class="mt-1 text-sm text-red-600">{{ errors.category }}</p>
                                </div>

                                <div>
                                    <label for="brand" class="block text-sm font-medium text-gray-700">Brand</label>
                                    <input
                                        id="brand"
                                        v-model="form.brand"
                                        type="text"
                                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                        :class="{ 'border-red-300': errors.brand }"
                                    />
                                    <p v-if="errors.brand" class="mt-1 text-sm text-red-600">{{ errors.brand }}</p>
                                </div>

                                <div>
                                    <label for="status" class="block text-sm font-medium text-gray-700">Stato *</label>
                                    <select
                                        id="status"
                                        v-model="form.status"
                                        required
                                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                        :class="{ 'border-red-300': errors.status }"
                                    >
                                        <option value="">Seleziona stato</option>
                                        <option value="available">Disponibile</option>
                                        <option value="not_available">Non disponibile</option>
                                        <option value="maintenance">In manutenzione</option>
                                        <option value="reserved">Riservato</option>
                                    </select>
                                    <p v-if="errors.status" class="mt-1 text-sm text-red-600">{{ errors.status }}</p>
                                </div>

                                <div>
                                    <label for="quantity" class="block text-sm font-medium text-gray-700">Quantità *</label>
                                    <input
                                        id="quantity"
                                        v-model.number="form.quantity"
                                        type="number"
                                        min="0"
                                        required
                                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                        :class="{ 'border-red-300': errors.quantity }"
                                    />
                                    <p v-if="errors.quantity" class="mt-1 text-sm text-red-600">{{ errors.quantity }}</p>
                                </div>

                                <div>
                                    <label for="serial_number" class="block text-sm font-medium text-gray-700">Numero Seriale</label>
                                    <input
                                        id="serial_number"
                                        v-model="form.serial_number"
                                        type="text"
                                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                        :class="{ 'border-red-300': errors.serial_number }"
                                    />
                                    <p v-if="errors.serial_number" class="mt-1 text-sm text-red-600">{{ errors.serial_number }}</p>
                                </div>
                            </div>

                            <div class="mt-6">
                                <label for="description" class="block text-sm font-medium text-gray-700">Descrizione</label>
                                <textarea
                                    id="description"
                                    v-model="form.description"
                                    rows="3"
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                    :class="{ 'border-red-300': errors.description }"
                                ></textarea>
                                <p v-if="errors.description" class="mt-1 text-sm text-red-600">{{ errors.description }}</p>
                            </div>
                        </div>

                        <!-- Location and Details -->
                        <div class="border-b border-gray-200 pb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Posizione e Dettagli</h3>
                            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                <div>
                                    <label for="location" class="block text-sm font-medium text-gray-700">Posizione</label>
                                    <input
                                        id="location"
                                        v-model="form.location"
                                        type="text"
                                        placeholder="Es: Magazzino IT - Scaffale A1"
                                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                        :class="{ 'border-red-300': errors.location }"
                                    />
                                    <p v-if="errors.location" class="mt-1 text-sm text-red-600">{{ errors.location }}</p>
                                </div>

                                <div>
                                    <label for="purchase_price" class="block text-sm font-medium text-gray-700">Prezzo di Acquisto (€)</label>
                                    <input
                                        id="purchase_price"
                                        v-model.number="form.purchase_price"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                        :class="{ 'border-red-300': errors.purchase_price }"
                                    />
                                    <p v-if="errors.purchase_price" class="mt-1 text-sm text-red-600">{{ errors.purchase_price }}</p>
                                </div>

                                <div>
                                    <label for="purchase_date" class="block text-sm font-medium text-gray-700">Data di Acquisto</label>
                                    <input
                                        id="purchase_date"
                                        v-model="form.purchase_date"
                                        type="date"
                                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                        :class="{ 'border-red-300': errors.purchase_date }"
                                    />
                                    <p v-if="errors.purchase_date" class="mt-1 text-sm text-red-600">{{ errors.purchase_date }}</p>
                                </div>

                                <div>
                                    <label for="warranty_expiry" class="block text-sm font-medium text-gray-700">Scadenza Garanzia</label>
                                    <input
                                        id="warranty_expiry"
                                        v-model="form.warranty_expiry"
                                        type="date"
                                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                        :class="{ 'border-red-300': errors.warranty_expiry }"
                                    />
                                    <p v-if="errors.warranty_expiry" class="mt-1 text-sm text-red-600">{{ errors.warranty_expiry }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Note Aggiuntive</h3>
                            <div>
                                <label for="notes" class="block text-sm font-medium text-gray-700">Note</label>
                                <textarea
                                    id="notes"
                                    v-model="form.notes"
                                    rows="3"
                                    placeholder="Note aggiuntive sull'articolo..."
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                    :class="{ 'border-red-300': errors.notes }"
                                ></textarea>
                                <p v-if="errors.notes" class="mt-1 text-sm text-red-600">{{ errors.notes }}</p>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="flex justify-end space-x-3">
                            <Link 
                                :href="route('warehouse.items.manage')"
                                class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                            >
                                Annulla
                            </Link>
                            <button
                                type="submit"
                                :disabled="processing"
                                class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
                            >
                                <svg v-if="processing" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                {{ processing ? 'Salvataggio...' : 'Crea Articolo' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script>
import AppLayout from '@/Layouts/AppLayout.vue'
import { Link, useForm } from '@inertiajs/vue3'

export default {
    components: {
        AppLayout,
        Link,
    },

    props: {
        categories: Array,
        statuses: Array,
        errors: Object,
    },

    setup() {
        const form = useForm({
            name: '',
            category: '',
            custom_category: '',
            brand: '',
            status: '',
            description: '',
            quantity: 1,
            serial_number: '',
            location: '',
            purchase_price: '',
            purchase_date: '',
            warranty_expiry: '',
            notes: '',
        })

        return { form }
    },

    data() {
        return {
            processing: false,
        }
    },

    methods: {
        submit() {
            this.processing = true
            
            // Se è stata selezionata una categoria personalizzata, usa quella
            if (this.form.category === 'custom' && this.form.custom_category) {
                this.form.category = this.form.custom_category
            }

            this.form.post(route('warehouse.items.store'), {
                onSuccess: () => {
                    this.processing = false
                },
                onError: () => {
                    this.processing = false
                }
            })
        }
    }
}
</script>
