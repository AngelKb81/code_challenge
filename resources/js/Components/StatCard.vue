<template>
  <div class="bg-white rounded-lg p-6 shadow">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-lg font-medium text-gray-900">{{ title }}</h3>
      <div class="flex items-center">
        <div class="w-8 h-8 rounded-full flex items-center justify-center" :class="colorClasses[color].bg">
          <div v-html="iconComponent" :class="iconClass"></div>
        </div>
      </div>
    </div>
    
    <div class="flex items-baseline">
      <p class="text-3xl font-bold" :class="valueClass">
        {{ formattedValue }}
      </p>
      <p v-if="subtitle" class="ml-2 text-sm text-gray-500">
        {{ subtitle }}
      </p>
    </div>
    
    <div v-if="trend" class="mt-2 flex items-center">
      <svg v-if="trend.direction === 'up'" class="w-4 h-4 text-green-500 mr-1" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd" d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z" clip-rule="evenodd"></path>
      </svg>
      <svg v-else-if="trend.direction === 'down'" class="w-4 h-4 text-red-500 mr-1" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd" d="M12 13a1 1 0 100 2h5a1 1 0 001-1V9a1 1 0 10-2 0v2.586l-4.293-4.293a1 1 0 00-1.414 0L8 9.586l-4.293-4.293a1 1 0 00-1.414 1.414l5 5a1 1 0 001.414 0L11 9.414 14.586 13H12z" clip-rule="evenodd"></path>
      </svg>
      <span class="text-xs font-medium" :class="trendClass">
        {{ trend.text }}
      </span>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  title: {
    type: String,
    required: true
  },
  value: {
    type: [Number, String],
    required: true
  },
  subtitle: {
    type: String,
    default: ''
  },
  icon: {
    type: String,
    default: 'chart-bar'
  },
  color: {
    type: String,
    default: 'blue',
    validator: (value) => ['blue', 'green', 'purple', 'orange', 'red', 'yellow'].includes(value)
  },
  trend: {
    type: Object,
    default: null
  }
})

const formattedValue = computed(() => {
  if (typeof props.value === 'number') {
    return props.value.toLocaleString()
  }
  return props.value
})

const colorClasses = {
  blue: {
    icon: 'text-blue-600',
    value: 'text-gray-900',
    bg: 'bg-blue-50'
  },
  green: {
    icon: 'text-green-600',
    value: 'text-gray-900',
    bg: 'bg-green-50'
  },
  purple: {
    icon: 'text-purple-600',
    value: 'text-gray-900',
    bg: 'bg-purple-50'
  },
  orange: {
    icon: 'text-orange-600',
    value: 'text-gray-900',
    bg: 'bg-orange-50'
  },
  red: {
    icon: 'text-red-600',
    value: 'text-gray-900',
    bg: 'bg-red-50'
  },
  yellow: {
    icon: 'text-yellow-600',
    value: 'text-gray-900',
    bg: 'bg-yellow-50'
  }
}

const iconClass = computed(() => colorClasses[props.color].icon)
const valueClass = computed(() => colorClasses[props.color].value)

const trendClass = computed(() => {
  if (!props.trend) return ''
  
  return {
    'text-green-600': props.trend.direction === 'up',
    'text-red-600': props.trend.direction === 'down',
    'text-gray-600': props.trend.direction === 'stable'
  }
})

// Icon components mapping
const iconComponents = {
  'chart-bar': `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
  </svg>`,
  'cube': `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
  </svg>`,
  'clipboard-list': `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
  </svg>`,
  'users': `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a4 4 0 11-8 0 4 4 0 018 0z" />
  </svg>`,
  'archive': `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
  </svg>`
}

const iconComponent = computed(() => iconComponents[props.icon] || iconComponents['chart-bar'])
</script>
