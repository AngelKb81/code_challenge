<template>
    <div id="app">
        <!-- Navigation -->
        <nav class="bg-white shadow-sm border-b">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <!-- Logo -->
                        <div class="shrink-0 flex items-center">
                            <Link :href="route('dashboard')" class="text-xl font-semibold text-gray-800">
                                Code Challenge
                            </Link>
                        </div>

                        <!-- Navigation Links -->
                        <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                            <NavLink :href="route('dashboard')" :active="route().current('dashboard')">
                                Dashboard
                            </NavLink>
                            <template v-if="$page.props.auth.user">
                                <!-- Dropdown Magazzino -->
                                <div class="relative" @mouseenter="showWarehouseMenu = true" @mouseleave="showWarehouseMenu = false">
                                    <NavLink :href="route('warehouse.index')" :active="route().current('warehouse.*')" class="flex items-center">
                                        Magazzino
                                        <svg class="ml-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </NavLink>
                                    <!-- Dropdown Menu -->
                                    <div v-show="showWarehouseMenu" class="absolute left-0 mt-1 w-56 bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 z-50">
                                        <div class="py-1">
                                            <Link :href="route('warehouse.index')" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                <svg class="inline w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h2a2 2 0 012 2v2H8V5z" />
                                                </svg>
                                                Dashboard Magazzino
                                            </Link>
                                            <Link :href="route('warehouse.items')" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                <svg class="inline w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                                </svg>
                                                Visualizza Inventario
                                            </Link>
                                            <Link :href="route('warehouse.requests')" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                <svg class="inline w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                                </svg>
                                                {{ $page.props.auth.user.role === 'admin' ? 'Gestisci Richieste' : 'Le Mie Richieste' }}
                                            </Link>
                                            <Link :href="route('warehouse.requests.create')" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                <svg class="inline w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                                </svg>
                                                Nuova Richiesta
                                            </Link>
                                            <template v-if="$page.props.auth.user.role === 'admin'">
                                                <hr class="my-1">
                                                <div class="px-4 py-2 text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Amministrazione
                                                </div>
                                                <Link :href="route('warehouse.items.manage')" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                    <svg class="inline w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    </svg>
                                                    Gestisci Articoli
                                                </Link>
                                                <Link :href="route('warehouse.items.create')" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                    <svg class="inline w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                                    </svg>
                                                    Nuovo Articolo
                                                </Link>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="hidden sm:flex sm:items-center sm:ml-6">
                        <!-- Settings Dropdown -->
                        <div class="ml-3 relative" v-if="$page.props.auth.user">
                            <Dropdown align="right" width="48">
                                <template #trigger>
                                    <span class="inline-flex rounded-md">
                                        <button type="button" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                                            {{ $page.props.auth.user.name }}

                                            <svg class="ml-2 -mr-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                    </span>
                                </template>

                                <template #content>
                                    <DropdownLink :href="route('logout')" method="post" as="button">
                                        Log Out
                                    </DropdownLink>
                                </template>
                            </Dropdown>
                        </div>

                        <!-- Guest Links -->
                        <div v-else class="space-x-4">
                            <Link :href="route('login')" class="text-gray-600 hover:text-gray-900">Login</Link>
                        </div>
                    </div>

                    <!-- Hamburger -->
                    <div class="-mr-2 flex items-center sm:hidden">
                        <button @click="showingNavigationDropdown = ! showingNavigationDropdown" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                            <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                <path :class="{'hidden': showingNavigationDropdown, 'inline-flex': ! showingNavigationDropdown }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                <path :class="{'hidden': ! showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Responsive Navigation Menu -->
            <div :class="{'block': showingNavigationDropdown, 'hidden': ! showingNavigationDropdown}" class="sm:hidden">
                <div class="pt-2 pb-3 space-y-1">
                    <ResponsiveNavLink :href="route('dashboard')" :active="route().current('dashboard')">
                        Dashboard
                    </ResponsiveNavLink>
                    <template v-if="$page.props.auth.user">
                        <!-- Magazzino Menu -->
                        <div class="border-t border-gray-200 pt-2">
                            <div class="px-3 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Magazzino
                            </div>
                            <ResponsiveNavLink :href="route('warehouse.index')" :active="route().current('warehouse.index')">
                                Dashboard Magazzino
                            </ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('warehouse.items')" :active="route().current('warehouse.items')">
                                Visualizza Inventario
                            </ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('warehouse.requests')" :active="route().current('warehouse.requests')">
                                {{ $page.props.auth.user.role === 'admin' ? 'Gestisci Richieste' : 'Le Mie Richieste' }}
                            </ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('warehouse.requests.create')" :active="route().current('warehouse.requests.create')">
                                Nuova Richiesta
                            </ResponsiveNavLink>
                            <template v-if="$page.props.auth.user.role === 'admin'">
                                <div class="px-3 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                    Amministrazione
                                </div>
                                <ResponsiveNavLink :href="route('warehouse.items.manage')" :active="route().current('warehouse.items.manage')">
                                    Gestisci Articoli
                                </ResponsiveNavLink>
                                <ResponsiveNavLink :href="route('warehouse.items.create')" :active="route().current('warehouse.items.create')">
                                    Nuovo Articolo
                                </ResponsiveNavLink>
                            </template>
                        </div>
                    </template>
                </div>

                <!-- Responsive Settings Options -->
                <div class="pt-4 pb-1 border-t border-gray-200" v-if="$page.props.auth.user">
                    <div class="px-4">
                        <div class="font-medium text-base text-gray-800">{{ $page.props.auth.user.name }}</div>
                        <div class="font-medium text-sm text-gray-500">{{ $page.props.auth.user.email }}</div>
                    </div>

                    <div class="mt-3 space-y-1">
                        <ResponsiveNavLink :href="route('logout')" method="post" as="button">
                            Log Out
                        </ResponsiveNavLink>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Page Heading -->
        <header class="bg-white shadow" v-if="$slots.header">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                <slot name="header" />
            </div>
        </header>

        <!-- Page Content -->
        <main>
            <slot />
        </main>
    </div>
</template>

<script>
import { Link } from '@inertiajs/vue3'
import Dropdown from '@/Components/Dropdown.vue'
import DropdownLink from '@/Components/DropdownLink.vue'
import NavLink from '@/Components/NavLink.vue'
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue'

export default {
    components: {
        Link,
        Dropdown,
        DropdownLink,
        NavLink,
        ResponsiveNavLink,
    },

    data() {
        return {
            showingNavigationDropdown: false,
            showWarehouseMenu: false,
        }
    },
}
</script>
