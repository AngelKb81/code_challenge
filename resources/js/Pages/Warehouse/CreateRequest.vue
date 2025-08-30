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
                                        >
                                            {{ item.name }} - {{ item.category }}
                                            <!-- OPZIONE A per Item Non Disponibili: Badge + Status nel select -->
                                            <span v-if="item.status !== 'available'">
                                                (Non disponibile)
                                            </span>
                                            <span v-else-if="item.available_quantity > 0">
                                                (Disponibile subito - {{ item.available_quantity }} unità)
                                            </span>
                                            <span v-else>
                                                (Esaurito)
                                            </span>
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

                                <!-- Disponibilità Item (Opzione A semplificata) -->
                                <div v-if="selectedItem" class="md:col-span-2">
                                    <!-- Badge di disponibilità semplificato -->
                                    <div class="mb-4">
                                        <!-- Item Non Disponibile per Status -->
                                        <div v-if="selectedItem.status !== 'available'" class="bg-red-50 border border-red-200 p-4 rounded-lg">
                                            <div class="flex items-center">
                                                <svg class="w-5 h-5 mr-2 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                                </svg>
                                                <span class="font-medium text-red-800">Non Disponibile</span>
                                            </div>
                                        </div>
                                        
                                        <!-- Item Disponibile Subito -->
                                        <div v-else-if="selectedItem.available_quantity > 0" class="bg-green-50 border border-green-200 p-4 rounded-lg">
                                            <div class="flex items-center">
                                                <svg class="w-5 h-5 mr-2 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                </svg>
                                                <span class="font-medium text-green-800">Disponibile Subito</span>
                                                <span class="ml-2 text-green-700">({{ selectedItem.available_quantity }} unità)</span>
                                            </div>
                                        </div>
                                        
                                        <!-- Item Esaurito ma con possibili date future -->
                                        <div v-else class="bg-red-50 border border-red-200 p-4 rounded-lg">
                                            <div class="flex items-center mb-2">
                                                <svg class="w-5 h-5 mr-2 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                                </svg>
                                                <span class="font-medium text-red-800">Esaurito</span>
                                            </div>
                                            <!-- Prima data disponibile se esiste -->
                                            <div v-if="selectedItem.availability && selectedItem.availability.next_available_date" class="text-sm text-red-700">
                                                Prima data disponibile: <strong>{{ formatDate(selectedItem.availability.next_available_date) }}</strong>
                                            </div>
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

                                <!-- Date (solo per existing items con suggerimento automatico) -->
                                <div v-if="form.request_type === 'existing_item'" class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <!-- Data Inizio con suggerimento automatico -->
                                    <div>
                                        <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">
                                            Data Inizio *
                                        </label>
                                        <!-- Suggerimento automatico se item non immediatamente disponibile -->
                                        <div v-if="selectedItem && selectedItem.available_quantity === 0 && selectedItem.availability?.next_available_date" class="mb-2 p-2 bg-blue-50 border border-blue-200 rounded text-sm">
                                            <div class="flex items-center text-blue-700">
                                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                                </svg>
                                                Suggerimento: prima data disponibile {{ formatDate(selectedItem.availability.next_available_date) }}
                                            </div>
                                            <button 
                                                type="button"
                                                @click="useSuggestedDate"
                                                class="mt-1 text-xs text-blue-600 hover:text-blue-800 underline"
                                            >
                                                Usa questa data
                                            </button>
                                        </div>
                                        <input
                                            id="start_date"
                                            type="date"
                                            v-model="form.start_date"
                                            :min="getMinStartDate()"
                                            @change="validateDates"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                            required
                                        >
                                        <div v-if="form.errors.start_date" class="mt-1 text-sm text-red-600">
                                            {{ form.errors.start_date }}
                                        </div>
                                        <!-- Validazione in tempo reale -->
                                        <div v-if="dateValidationMessage" class="mt-1 text-sm" :class="dateValidationMessage.type === 'error' ? 'text-red-600' : 'text-orange-600'">
                                            {{ dateValidationMessage.message }}
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
                                            :min="form.start_date || getMinStartDate()"
                                            @change="validateDates"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                            required
                                        >
                                        <div v-if="form.errors.end_date" class="mt-1 text-sm text-red-600">
                                            {{ form.errors.end_date }}
                                        </div>
                                    </div>
                                </div>

                                <!-- Validazione Disponibilità in Tempo Reale -->
                                <div v-if="form.request_type === 'existing_item' && selectedItem && form.start_date && form.end_date" class="md:col-span-2">
                                    <div v-if="availabilityValidation.loading" class="bg-blue-50 border border-blue-200 p-4 rounded-lg">
                                        <div class="flex items-center">
                                            <svg class="animate-spin h-5 w-5 mr-2 text-blue-600" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            <span class="text-blue-700">Verifica disponibilità...</span>
                                        </div>
                                    </div>

                                    <div v-else-if="availabilityValidation.result" class="rounded-lg p-4" :class="[
                                        availabilityValidation.result.available 
                                            ? 'bg-green-50 border border-green-200' 
                                            : 'bg-red-50 border border-red-200'
                                    ]">
                                        <div class="flex items-start">
                                            <svg class="h-5 w-5 mr-2 mt-0.5" :class="[
                                                availabilityValidation.result.available ? 'text-green-600' : 'text-red-600'
                                            ]" fill="currentColor" viewBox="0 0 20 20">
                                                <path v-if="availabilityValidation.result.available" fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                <path v-else fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                            </svg>
                                            <div class="flex-1">
                                                <p class="font-medium" :class="[
                                                    availabilityValidation.result.available ? 'text-green-800' : 'text-red-800'
                                                ]">
                                                    {{ availabilityValidation.result.message }}
                                                </p>
                                                
                                                <!-- Richieste conflittuali -->
                                                <div v-if="!availabilityValidation.result.available && availabilityValidation.result.conflicting_requests && availabilityValidation.result.conflicting_requests.length > 0" class="mt-3">
                                                    <p class="text-red-700 text-sm font-medium mb-2">Richieste esistenti in conflitto:</p>
                                                    <div class="space-y-2 mb-4">
                                                        <div 
                                                            v-for="(conflict, index) in availabilityValidation.result.conflicting_requests" 
                                                            :key="index"
                                                            class="bg-red-50 border border-red-200 rounded p-3 text-sm"
                                                        >
                                                            <div class="flex justify-between items-start">
                                                                <div>
                                                                    <p class="font-medium text-red-800">{{ conflict.user_name }}</p>
                                                                    <p class="text-red-700">{{ conflict.start_date }} → {{ conflict.end_date }}</p>
                                                                    <p class="text-red-600 text-xs">{{ conflict.quantity }} unità - {{ conflict.reason }}</p>
                                                                </div>
                                                                <span class="text-xs text-red-500 bg-red-100 px-2 py-1 rounded">
                                                                    ID: {{ conflict.id }}
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
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

                                <!-- Quantità Richiesta - Existing Item (Opzione A per gestione esauriti) -->
                                <div v-if="form.request_type === 'existing_item'">
                                    <label for="quantity_requested" class="block text-sm font-medium text-gray-700 mb-2">
                                        Quantità Richiesta
                                    </label>
                                    <input
                                        id="quantity_requested"
                                        type="number"
                                        v-model="form.quantity_requested"
                                        min="1"
                                        :max="getMaxQuantityForCurrentSelection()"
                                        :disabled="!selectedItem || selectedItem.status !== 'available'"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 disabled:bg-gray-100 disabled:cursor-not-allowed"
                                    >
                                    <!-- Messaggi informativi -->
                                    <div v-if="selectedItem" class="mt-1 text-sm">
                                        <div v-if="selectedItem.status !== 'available'" class="text-red-600">
                                            Questo articolo non è disponibile
                                        </div>
                                        <div v-else-if="selectedItem.available_quantity === 0" class="text-red-600">
                                            Articolo esaurito - seleziona date future per la prenotazione
                                        </div>
                                        <div v-else class="text-green-600">
                                            Fino a {{ selectedItem.available_quantity }} unità disponibili subito
                                        </div>
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
                                    :disabled="form.processing || isSubmitDisabled()"
                                    :class="[
                                        'px-6 py-2 rounded-md transition duration-200',
                                        isSubmitDisabled() 
                                            ? 'bg-gray-400 text-gray-700 cursor-not-allowed' 
                                            : 'bg-blue-600 text-white hover:bg-blue-700'
                                    ]"
                                >
                                    <span v-if="form.processing">Invio in corso...</span>
                                    <span v-else-if="isItemUnavailable()">Esaurito</span>
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
import { useForm, usePage } from '@inertiajs/vue3'
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
const dateValidationMessage = ref(null)
const availabilityValidation = ref({
    loading: false,
    result: null
})

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
        // Reset date validation quando cambia item
        dateValidationMessage.value = null
        
        // Auto-suggerimento data se item non immediatamente disponibile
        if (selectedItem.value.available_quantity === 0 && selectedItem.value.availability?.next_available_date) {
            // Non auto-compila, ma prepara il suggerimento (gestito nel template)
        }
    }
}

// Nuovi metodi per le funzionalità implementate
const getMinStartDate = () => {
    if (selectedItem.value && selectedItem.value.available_quantity === 0 && selectedItem.value.availability?.next_available_date) {
        // Se item non disponibile subito, suggerisci la prima data disponibile
        return selectedItem.value.availability.next_available_date
    }
    return today.value
}

const getMaxQuantityForCurrentSelection = () => {
    if (!selectedItem.value || selectedItem.value.status !== 'available') {
        return 0
    }
    
    // Se disponibile subito, usa available_quantity
    if (selectedItem.value.available_quantity > 0) {
        return selectedItem.value.available_quantity
    }
    
    // Se esaurito ma ha date future disponibili, permetti la prenotazione
    return selectedItem.value.quantity || 1
}

const useSuggestedDate = () => {
    if (selectedItem.value?.availability?.next_available_date) {
        form.start_date = selectedItem.value.availability.next_available_date
        validateDates()
    }
}

const validateDates = async () => {
    dateValidationMessage.value = null
    availabilityValidation.value.result = null
    
    if (!selectedItem.value || !selectedItem.value.id || !form.start_date || !form.end_date || !form.quantity_requested) {
        return
    }
    
    // Mostra indicatore di caricamento
    availabilityValidation.value.loading = true
    
    try {
        // Costruisci l'URL in modo sicuro
        let apiUrl;
        try {
            if (typeof route === 'function') {
                const routeName = 'warehouse.requests.validate-availability';
                apiUrl = route(routeName);
            } else {
                throw new Error('Route function not available');
            }
        } catch (routeError) {
            // Fallback: costruisci l'URL manualmente
            console.warn('Route function error, using fallback URL:', routeError);
            apiUrl = '/warehouse/requests/validate-availability';
        }
        
        const response = await fetch(apiUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': usePage().props.csrf_token || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                item_id: selectedItem.value.id,
                start_date: form.start_date,
                end_date: form.end_date,
                quantity_requested: form.quantity_requested
            })
        })
        
        if (response.ok) {
            const result = await response.json()
            availabilityValidation.value.result = result
            
            // Aggiorna anche il messaggio semplice per compatibilità
            if (result.available) {
                dateValidationMessage.value = {
                    type: 'success',
                    message: 'Periodo disponibile'
                }
            } else {
                dateValidationMessage.value = {
                    type: 'error',
                    message: result.message
                }
            }
        } else {
            // Gestione più specifica degli errori HTTP
            let errorMessage = 'Errore durante la verifica della disponibilità'
            
            if (response.status === 401) {
                errorMessage = 'Accesso non autorizzato. Ricarica la pagina e riprova.'
            } else if (response.status === 403) {
                errorMessage = 'Non hai i permessi per questa operazione.'
            } else if (response.status === 404) {
                errorMessage = 'Endpoint di validazione non trovato.'
            } else if (response.status === 422) {
                const errorData = await response.json().catch(() => ({}))
                errorMessage = errorData.message || 'Dati non validi per la validazione.'
            } else if (response.status >= 500) {
                errorMessage = 'Errore del server. Riprova più tardi.'
            }
            
            console.error('HTTP Error:', response.status, response.statusText)
            dateValidationMessage.value = {
                type: 'error',
                message: errorMessage
            }
            availabilityValidation.value.result = null
        }
    } catch (error) {
        console.error('Errore validazione disponibilità:', error)
        
        // Gestione più specifica degli errori di rete/parsing
        let errorMessage = 'Errore durante la verifica della disponibilità'
        
        if (error.name === 'TypeError' && error.message.includes('fetch')) {
            errorMessage = 'Errore di connessione. Verifica la connessione di rete.'
        } else if (error.name === 'SyntaxError' && error.message.includes('JSON')) {
            errorMessage = 'Errore nel formato dei dati ricevuti dal server.'
        } else if (error.message.includes('Route function not available')) {
            errorMessage = 'Errore di routing. Ricarica la pagina e riprova.'
        }
        
        dateValidationMessage.value = {
            type: 'error',
            message: errorMessage
        }
        availabilityValidation.value.result = null
    } finally {
        availabilityValidation.value.loading = false
    }
}

// Metodi per gestire la disabilitazione del pulsante (Opzione A)
const isItemUnavailable = () => {
    if (form.request_type !== 'existing_item' || !selectedItem.value) {
        return false
    }
    
    return selectedItem.value.status !== 'available' || 
           (selectedItem.value.available_quantity === 0 && !selectedItem.value.availability?.next_available_date)
}

const isSubmitDisabled = () => {
    if (form.processing) {
        return true
    }
    
    if (form.request_type === 'existing_item') {
        // Disabilita se item non selezionato
        if (!selectedItem.value) {
            return true
        }
        
        // Disabilita se item non disponibile (Opzione A)
        if (selectedItem.value.status !== 'available') {
            return true
        }
        
        // Disabilita se item esaurito e nessuna data futura disponibile
        if (selectedItem.value.available_quantity === 0 && !selectedItem.value.availability?.next_available_date) {
            return true
        }
        
        // Disabilita se ci sono errori di validazione delle date
        if (dateValidationMessage.value?.type === 'error') {
            return true
        }
        
        // Disabilita se la validazione della disponibilità è in corso
        if (availabilityValidation.value.loading) {
            return true
        }
        
        // Disabilita se la validazione ha determinato che il periodo non è disponibile
        if (availabilityValidation.value.result && !availabilityValidation.value.result.available) {
            return true
        }
        
        // Disabilita se date non inserite per item con available_quantity = 0
        if (selectedItem.value.available_quantity === 0 && (!form.start_date || !form.end_date)) {
            return true
        }
    }
    
    return false
}

const submit = () => {
    // Reset campi non necessari in base al tipo di richiesta
    if (form.request_type === 'existing_item') {
        // Validazione per item esistente
        if (!selectedItem.value) {
            alert('Seleziona un articolo prima di procedere');
            return;
        }
        
        // OPZIONE A: Permetti prenotazioni per item esauriti se hanno date future disponibili
        if (selectedItem.value.status !== 'available') {
            alert('L\'articolo selezionato non è disponibile');
            return;
        }
        
        // Se item disponibile subito, controlla quantità
        if (selectedItem.value.available_quantity > 0) {
            if (form.quantity_requested > selectedItem.value.available_quantity) {
                alert(`Quantità non disponibile subito. Massimo ${selectedItem.value.available_quantity} unità disponibili ora`);
                return;
            }
        } else {
            // Item esaurito - permetti prenotazione solo con date valide
            if (!form.start_date || !form.end_date) {
                alert('Per articoli esauriti, specifica le date di prenotazione');
                return;
            }
            
            if (dateValidationMessage.value?.type === 'error') {
                alert('Le date selezionate non sono valide per questo articolo');
                return;
            }
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

// Watch for changes that require validation
watch(() => [form.start_date, form.end_date, form.quantity_requested], () => {
    if (form.request_type === 'existing_item' && selectedItem.value) {
        validateDates()
    }
}, { deep: true })

// Reset validation when item changes
watch(() => form.item_id, () => {
    availabilityValidation.value.result = null
    dateValidationMessage.value = null
})
</script>
