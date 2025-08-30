<template>
    <AppLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-medium mb-4">Benvenuto nel Code Challenge!</h3>
                        <p class="mb-4">
                            Questa è la dashboard dell'applicazione Laravel 11 + Vue 3 + Inertia.js.
                        </p>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-6">
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                <h4 class="font-semibold text-blue-800">Laravel 11</h4>
                                <p class="text-blue-600 text-sm">Framework PHP moderno e potente</p>
                            </div>
                            
                            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                                <h4 class="font-semibold text-green-800">Vue 3</h4>
                                <p class="text-green-600 text-sm">Framework JavaScript progressivo</p>
                            </div>
                            
                            <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                                <h4 class="font-semibold text-purple-800">Inertia.js</h4>
                                <p class="text-purple-600 text-sm">Ponte moderno tra backend e frontend</p>
                            </div>
                        </div>

                        <div v-if="$page.props.auth.user" class="mt-6 p-4 bg-gray-50 rounded-lg">
                            <h4 class="font-semibold text-gray-800">Informazioni Utente</h4>
                            <p class="text-sm text-gray-600">Nome: {{ $page.props.auth.user.name }}</p>
                            <p class="text-sm text-gray-600">Email: {{ $page.props.auth.user.email }}</p>
                            <p class="text-sm text-gray-600">Ruolo: {{ $page.props.auth.user.role || 'user' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script>
import AppLayout from '@/Layouts/AppLayout.vue'

export default {
    components: {
        AppLayout,
    },
}
</script>
