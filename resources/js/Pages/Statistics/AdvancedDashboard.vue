<template>
  <AppLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <div>
          <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            📊 Dashboard Statistiche Avanzate
          </h2>
          <p class="text-gray-600 text-sm mt-1">
            Analytics complete e insights per la gestione del magazzino (Solo Admin)
          </p>
        </div>
        <div class="flex space-x-2">
          <Link 
            :href="route('dashboard')" 
            class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition duration-200 flex items-center"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Dashboard Principale
          </Link>
          <Link 
            :href="route('warehouse.index')" 
            class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition duration-200 flex items-center"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
            </svg>
            Magazzino
          </Link>
        </div>
      </div>
    </template>

    <!-- Access Denied Message for Non-Admin -->
    <div v-if="!isAdmin" class="py-12">
      <div class="max-w-md mx-auto">
        <div class="bg-red-50 border border-red-200 rounded-lg p-6 text-center">
          <svg class="mx-auto h-12 w-12 text-red-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 15.5c-.77.833.192 2.5 1.732 2.5z"></path>
          </svg>
          <h3 class="text-lg font-medium text-red-800 mb-2">Accesso Negato</h3>
          <p class="text-red-600 mb-4">
            Questa pagina è disponibile solo per gli amministratori.
          </p>
          <Link 
            :href="route('dashboard')"
            class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition duration-200"
          >
            Torna al Dashboard
          </Link>
        </div>
      </div>
    </div>

    <!-- Admin Dashboard Content -->
    <div v-else class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
        
        <!-- Overview Cards with Trends -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          <StatCard
            title="Totale Articoli"
            :value="stats?.overview?.total_items || 0"
            subtitle="nel magazzino"
            icon="cube"
            color="blue"
            :trend="getItemsTrend()"
          />
          <StatCard
            title="Richieste Totali"
            :value="stats?.overview?.total_requests || 0"
            subtitle="in questo periodo"
            icon="clipboard-list"
            color="green"
            :trend="getRequestsTrend()"
          />
          <StatCard
            title="Utenti Attivi"
            :value="stats?.users?.active_users || 0"
            subtitle="nel periodo"
            icon="users"
            color="purple"
          />
          <StatCard
            title="Tasso Approvazione"
            :value="`${stats?.requests?.approval_percentage || 0}%`"
            subtitle="delle richieste"
            icon="check-circle"
            color="green"
          />
        </div>

        <!-- Charts Row 1: Request Trends and Status Distribution -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
          <!-- Daily Request Chart -->
          <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex items-center justify-between mb-6">
              <h3 class="text-lg font-semibold text-gray-900">Andamento Richieste Giornaliere</h3>
              <div class="flex items-center text-sm text-gray-500">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
                Ultimi {{ stats?.chart_data?.length || 0 }} giorni
              </div>
            </div>
            <RequestChart 
              v-if="stats?.chart_data" 
              :data="stats.chart_data" 
              :height="300"
            />
            <div v-else class="h-64 flex items-center justify-center text-gray-500">
              Nessun dato disponibile per il grafico
            </div>
            
            <!-- Peak day info -->
            <div v-if="stats?.trends?.peak_day" class="mt-4 p-3 bg-blue-50 rounded-lg">
              <div class="flex items-center text-sm">
                <svg class="w-4 h-4 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                </svg>
                <span class="text-blue-800">
                  <strong>Giorno di picco:</strong> {{ stats.trends.peak_day.formatted_date }} 
                  ({{ stats.trends.peak_day.requests }} richieste)
                </span>
              </div>
            </div>
          </div>

          <!-- Request Status Donut Chart -->
          <div class="bg-white rounded-lg shadow-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Distribuzione Stati Richieste</h3>
            <DonutChart
              title="Stati delle Richieste"
              :data="getRequestStatusData()"
            />
            
            <!-- Status Summary -->
            <div class="mt-6 grid grid-cols-3 gap-4 text-center">
              <div class="p-3 bg-green-50 rounded-lg">
                <div class="text-lg font-bold text-green-600">{{ stats?.requests?.approved || 0 }}</div>
                <div class="text-xs text-green-800">Approvate</div>
              </div>
              <div class="p-3 bg-yellow-50 rounded-lg">
                <div class="text-lg font-bold text-yellow-600">{{ stats?.requests?.pending || 0 }}</div>
                <div class="text-xs text-yellow-800">In Attesa</div>
              </div>
              <div class="p-3 bg-red-50 rounded-lg">
                <div class="text-lg font-bold text-red-600">{{ stats?.requests?.rejected || 0 }}</div>
                <div class="text-xs text-red-800">Rifiutate</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Charts Row 2: Items and Categories -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
          <!-- Most Requested Items Chart -->
          <div class="bg-white rounded-lg shadow-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Articoli Più Richiesti</h3>
            <div v-if="stats?.items?.most_requested?.length > 0" class="space-y-3">
              <div
                v-for="(item, index) in stats.items.most_requested.slice(0, 8)"
                :key="item.item_id"
                class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors"
              >
                <div class="flex items-center">
                  <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                    <span class="text-sm font-bold text-blue-600">{{ index + 1 }}</span>
                  </div>
                  <span class="font-medium text-gray-900">{{ item.name }}</span>
                </div>
                <div class="flex items-center">
                  <span class="text-lg font-bold text-gray-700 mr-2">{{ item.requests_count }}</span>
                  <span class="text-xs text-gray-500">richieste</span>
                </div>
              </div>
            </div>
            <div v-else class="h-64 flex items-center justify-center text-gray-500">
              Nessun dato disponibile
            </div>
          </div>

          <!-- Category Distribution -->
          <div class="bg-white rounded-lg shadow-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Richieste per Categoria</h3>
            <DonutChart
              title="Categorie Prodotti"
              :data="getCategoryData()"
            />
            
            <!-- Category stats -->
            <div class="mt-6 space-y-2">
              <div class="flex justify-between text-sm">
                <span class="text-gray-600">Totale categorie:</span>
                <span class="font-semibold">{{ stats?.items?.total_categories || 0 }}</span>
              </div>
              <div class="flex justify-between text-sm">
                <span class="text-gray-600">Articoli mai richiesti:</span>
                <span class="font-semibold text-red-600">{{ stats?.items?.items_never_requested || 0 }}</span>
              </div>
              <div class="flex justify-between text-sm">
                <span class="text-gray-600">Utilizzo medio articoli:</span>
                <span class="font-semibold text-blue-600">{{ stats?.items?.average_item_utilization || 0 }}%</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Charts Row 3: Users and Time Analysis -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
          <!-- Top Users -->
          <div class="bg-white rounded-lg shadow-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Utenti Più Attivi</h3>
            <div v-if="stats?.users?.top_requesters?.length > 0" class="space-y-3">
              <div
                v-for="(user, index) in stats.users.top_requesters.slice(0, 8)"
                :key="user.user_id"
                class="flex items-center justify-between p-3 bg-gradient-to-r from-purple-50 to-blue-50 rounded-lg"
              >
                <div class="flex items-center">
                  <div class="w-10 h-10 bg-gradient-to-r from-purple-500 to-blue-500 rounded-full flex items-center justify-center mr-3">
                    <span class="text-sm font-bold text-white">{{ user.name.charAt(0).toUpperCase() }}</span>
                  </div>
                  <div>
                    <div class="font-medium text-gray-900">{{ user.name }}</div>
                    <div class="text-xs text-gray-500">#{{ index + 1 }} classificato</div>
                  </div>
                </div>
                <div class="text-right">
                  <div class="text-lg font-bold text-gray-700">{{ user.requests_count }}</div>
                  <div class="text-xs text-gray-500">richieste</div>
                </div>
              </div>
            </div>
            <div v-else class="h-64 flex items-center justify-center text-gray-500">
              Nessun dato disponibile
            </div>

            <!-- User stats summary -->
            <div class="mt-6 pt-4 border-t border-gray-200">
              <div class="grid grid-cols-2 gap-4 text-center">
                <div>
                  <div class="text-lg font-bold text-purple-600">{{ stats?.users?.active_users || 0 }}</div>
                  <div class="text-xs text-gray-600">Utenti attivi</div>
                </div>
                <div>
                  <div class="text-lg font-bold text-gray-600">{{ stats?.users?.average_requests_per_user || 0 }}</div>
                  <div class="text-xs text-gray-600">Media richieste/utente</div>
                </div>
              </div>
            </div>
          </div>

          <!-- Time Analysis -->
          <div class="bg-white rounded-lg shadow-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Analisi Temporale</h3>
            
            <!-- Busiest Hour -->
            <div v-if="stats?.trends?.busiest_hour" class="mb-6 p-4 bg-yellow-50 rounded-lg">
              <div class="flex items-center mb-2">
                <svg class="w-5 h-5 mr-2 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span class="font-semibold text-yellow-800">Ora di Punta</span>
              </div>
              <div class="text-2xl font-bold text-yellow-700">{{ stats.trends.busiest_hour.formatted_hour }}</div>
              <div class="text-sm text-yellow-600">{{ stats.trends.busiest_hour.requests }} richieste in quest'ora</div>
            </div>

            <!-- Growth Trend -->
            <div v-if="stats?.trends?.requests_growth" class="mb-6 p-4 rounded-lg" 
                 :class="getTrendBgClass(stats.trends.requests_growth.trend)">
              <div class="flex items-center mb-2">
                <svg v-if="stats.trends.requests_growth.trend === 'up'" class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                </svg>
                <svg v-else-if="stats.trends.requests_growth.trend === 'down'" class="w-5 h-5 mr-2 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"></path>
                </svg>
                <svg v-else class="w-5 h-5 mr-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"></path>
                </svg>
                <span class="font-semibold" :class="getTrendTextClass(stats.trends.requests_growth.trend)">
                  Crescita Richieste
                </span>
              </div>
              <div class="text-2xl font-bold" :class="getTrendTextClass(stats.trends.requests_growth.trend)">
                {{ stats.trends.requests_growth.growth_percentage > 0 ? '+' : '' }}{{ stats.trends.requests_growth.growth_percentage }}%
              </div>
              <div class="text-sm" :class="getTrendTextClass(stats.trends.requests_growth.trend)">
                {{ stats.trends.requests_growth.current }} vs {{ stats.trends.requests_growth.previous }} (periodo precedente)
              </div>
            </div>

            <!-- Low Stock Alert -->
            <div v-if="stats?.items?.low_stock?.length > 0" class="p-4 bg-red-50 rounded-lg">
              <div class="flex items-center mb-2">
                <svg class="w-5 h-5 mr-2 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 15.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
                <span class="font-semibold text-red-800">Allarme Scorte Basse</span>
              </div>
              <div class="text-lg font-bold text-red-700">{{ stats.items.low_stock.length }}</div>
              <div class="text-sm text-red-600">articoli con scorte basse</div>
            </div>
          </div>
        </div>

        <!-- Additional Insights -->
        <div class="bg-white rounded-lg shadow-lg p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-6">📈 Insights e Raccomandazioni</h3>
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            
            <!-- Performance Insight -->
            <div class="p-4 border border-blue-200 rounded-lg bg-blue-50">
              <div class="flex items-center mb-2">
                <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
                <span class="font-semibold text-blue-800">Performance</span>
              </div>
              <div class="text-sm text-blue-700">
                Tasso di approvazione del {{ stats?.requests?.approval_percentage || 0 }}% 
                {{ (stats?.requests?.approval_percentage || 0) > 80 ? '- Eccellente!' : 
                   (stats?.requests?.approval_percentage || 0) > 60 ? '- Buono' : '- Da migliorare' }}
              </div>
            </div>

            <!-- Efficiency Insight -->
            <div class="p-4 border border-green-200 rounded-lg bg-green-50">
              <div class="flex items-center mb-2">
                <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                </svg>
                <span class="font-semibold text-green-800">Efficienza</span>
              </div>
              <div class="text-sm text-green-700">
                Utilizzo medio articoli: {{ stats?.items?.average_item_utilization || 0 }}%
                {{ (stats?.items?.average_item_utilization || 0) > 70 ? '- Ottimo utilizzo!' : 
                   (stats?.items?.average_item_utilization || 0) > 40 ? '- Utilizzo moderato' : '- Sottoutilizzato' }}
              </div>
            </div>

            <!-- Inventory Alert -->
            <div class="p-4 border border-orange-200 rounded-lg bg-orange-50">
              <div class="flex items-center mb-2">
                <svg class="w-5 h-5 mr-2 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                </svg>
                <span class="font-semibold text-orange-800">Inventario</span>
              </div>
              <div class="text-sm text-orange-700">
                {{ stats?.items?.items_never_requested || 0 }} articoli mai richiesti
                {{ (stats?.items?.items_never_requested || 0) > 0 ? '- Considera la rimozione o promozione' : '- Tutti gli articoli sono utilizzati!' }}
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>

  </AppLayout>
</template>

<script setup>
import { computed, onMounted } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import StatCard from '@/Components/StatCard.vue'
import RequestChart from '@/Components/RequestChart.vue'
import DonutChart from '@/Components/DonutChart.vue'

const page = usePage()

const props = defineProps({
  stats: Object,
  filters: Object
})

// Admin check
const currentUser = computed(() => page.props.auth?.user || null)
const isAdmin = computed(() => currentUser.value?.role === 'admin')

// Redirect if not admin
onMounted(() => {
  if (!isAdmin.value) {
    console.warn('Access denied: Statistics dashboard is admin-only')
    // You could also redirect here if needed
    // router.visit('/dashboard')
  }
})

// Computed methods for trend analysis
const getItemsTrend = () => {
  // Since we don't have previous period data for items, we'll use a simple calculation
  const total = props.stats?.overview?.total_items || 0
  return total > 0 ? {
    direction: 'stable',
    text: `${total} totali`
  } : null
}

const getRequestsTrend = () => {
  if (!props.stats?.trends?.requests_growth) return null
  
  const growth = props.stats.trends.requests_growth
  return {
    direction: growth.trend,
    text: `${growth.growth_percentage > 0 ? '+' : ''}${growth.growth_percentage}%`
  }
}

// Chart data formatters
const getRequestStatusData = () => {
  if (!props.stats?.requests) return []
  
  const data = []
  const requests = props.stats.requests
  
  if (requests.approved > 0) {
    data.push({
      label: 'Approvate',
      value: requests.approved,
      color: '#10b981'
    })
  }
  
  if (requests.pending > 0) {
    data.push({
      label: 'In Attesa',
      value: requests.pending,
      color: '#f59e0b'
    })
  }
  
  if (requests.rejected > 0) {
    data.push({
      label: 'Rifiutate',
      value: requests.rejected,
      color: '#ef4444'
    })
  }
  
  return data
}

const getCategoryData = () => {
  if (!props.stats?.items?.by_category) return []
  
  const colors = ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#06b6d4', '#84cc16', '#f97316']
  
  return props.stats.items.by_category.slice(0, 8).map((category, index) => ({
    label: category.category || 'Senza categoria',
    value: category.requests_count,
    color: colors[index % colors.length]
  }))
}

// Style helpers for trends
const getTrendBgClass = (trend) => {
  switch (trend) {
    case 'up': return 'bg-green-50'
    case 'down': return 'bg-red-50'
    default: return 'bg-gray-50'
  }
}

const getTrendTextClass = (trend) => {
  switch (trend) {
    case 'up': return 'text-green-700'
    case 'down': return 'text-red-700'
    default: return 'text-gray-700'
  }
}
</script>

<style scoped>
/* Custom scrollbar for better UX */
::-webkit-scrollbar {
  width: 6px;
}

::-webkit-scrollbar-track {
  background: #f1f5f9;
}

::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 3px;
}

::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}
</style>
