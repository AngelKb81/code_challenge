<template>
    <div class="min-h-screen bg-gray-100">
        <!-- Header -->
        <header class="bg-white shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center py-6">
                    <div class="flex items-center">
                        <h1 class="text-2xl font-bold text-gray-900">Code Challenge</h1>
                    </div>
                    <div class="space-x-4">
                        <Link v-if="!$page.props.auth.user" :href="route('login')" class="text-gray-600 hover:text-gray-900">
                            Login
                        </Link>
                        <Link v-else :href="route('dashboard')" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                            Dashboard
                        </Link>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="text-center">
                <h2 class="text-4xl font-bold text-gray-900 mb-6">
                    Benvenuto nel Code Challenge
                </h2>
                <p class="text-xl text-gray-600 mb-8">
                    Applicazione Laravel 11 + Vue 3 + Inertia.js con sistema di autenticazione e ruoli utente
                </p>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mt-12">
                    <div class="bg-white p-6 rounded-lg shadow-md">
                        <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center mx-auto mb-4">
                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Laravel 11</h3>
                        <p class="text-gray-600">
                            Framework PHP moderno con architettura MVC, ORM Eloquent e sistema di routing avanzato.
                        </p>
                    </div>

                    <div class="bg-white p-6 rounded-lg shadow-md">
                        <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mx-auto mb-4">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Vue 3</h3>
                        <p class="text-gray-600">
                            Framework JavaScript progressivo per costruire interfacce utente interattive e reattive.
                        </p>
                    </div>

                    <div class="bg-white p-6 rounded-lg shadow-md">
                        <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mx-auto mb-4">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Inertia.js</h3>
                        <p class="text-gray-600">
                            Ponte moderno tra backend e frontend che consente di creare SPA senza API complesse.
                        </p>
                    </div>
                </div>

                <div class="mt-12">
                    <Link v-if="!$page.props.auth.user" :href="route('login')" class="bg-blue-600 text-white px-8 py-3 rounded-md text-lg hover:bg-blue-700 transition-colors">
                        Accedi all'Applicazione
                    </Link>
                    <Link v-else :href="route('dashboard')" class="bg-green-600 text-white px-8 py-3 rounded-md text-lg hover:bg-green-700 transition-colors">
                        Vai alla Dashboard
                    </Link>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="bg-white border-t mt-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div class="text-center text-gray-600">
                    <p>&copy; 2025 Code Challenge. Creato con Laravel 11, Vue 3 e Inertia.js.</p>
                </div>
            </div>
        </footer>
    </div>
</template>

<script>
import { Link } from '@inertiajs/vue3'

export default {
    components: {
        Link,
    },
}
</script>
