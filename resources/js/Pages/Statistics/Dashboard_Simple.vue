<template>
  <AppLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <div>
          <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Statistics Dashboard
          </h2>
          <p class="text-gray-600 text-sm mt-1">
            Analytics and insights for warehouse management
          </p>
        </div>
        <div class="flex space-x-2">
          <Link 
            :href="route('dashboard')" 
            class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition duration-200"
          >
            ← Main Dashboard
          </Link>
          <Link 
            :href="route('warehouse.index')" 
            class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition duration-200"
          >
            🏠 Warehouse
          </Link>
        </div>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        
        <!-- Overview Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
          <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg font-semibold">Total Items</h3>
            <p class="text-3xl font-bold text-blue-600">{{ stats?.overview?.total_items || 0 }}</p>
          </div>
          <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg font-semibold">Total Requests</h3>
            <p class="text-3xl font-bold text-green-600">{{ stats?.overview?.total_requests || 0 }}</p>
          </div>
          <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg font-semibold">Active Users</h3>
            <p class="text-3xl font-bold text-purple-600">{{ stats?.overview?.total_users || 0 }}</p>
          </div>
          <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg font-semibold">Inventory</h3>
            <p class="text-3xl font-bold text-orange-600">{{ stats?.overview?.total_quantity || 0 }}</p>
          </div>
        </div>

        <!-- Simple Statistics -->
        <div class="bg-white rounded-lg shadow p-6">
          <h2 class="text-lg font-semibold text-gray-900 mb-6">Statistics Overview</h2>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            
            <!-- Most Requested Items -->
            <div>
              <h3 class="text-md font-medium text-gray-800 mb-4">Most Requested Items</h3>
              <div class="space-y-2" v-if="stats?.items?.most_requested">
                <div v-for="item in stats.items.most_requested.slice(0, 5)" :key="item.item_id"
                     class="flex justify-between p-3 bg-gray-50 rounded">
                  <span>{{ item.name }}</span>
                  <span class="font-semibold">{{ item.requests_count }} requests</span>
                </div>
              </div>
              <p v-else class="text-gray-500">No data available</p>
            </div>

            <!-- Top Users -->
            <div>
              <h3 class="text-md font-medium text-gray-800 mb-4">Top Requesters</h3>
              <div class="space-y-2" v-if="stats?.users?.top_requesters">
                <div v-for="user in stats.users.top_requesters.slice(0, 5)" :key="user.user_id"
                     class="flex justify-between p-3 bg-gray-50 rounded">
                  <span>{{ user.name }}</span>
                  <span class="font-semibold">{{ user.requests_count }} requests</span>
                </div>
              </div>
              <p v-else class="text-gray-500">No data available</p>
            </div>

          </div>
        </div>

      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  stats: Object,
  filters: Object
})
</script>
