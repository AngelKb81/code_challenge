<template>
  <div class="text-center">
    <h3 class="text-sm font-medium text-gray-900 mb-3">{{ title }}</h3>
    <div class="relative h-32 w-32 mx-auto">
      <canvas ref="chartCanvas" class="h-full w-full"></canvas>
    </div>
    <div class="mt-3 space-y-1">
      <div v-for="item in data" :key="item.label" 
           class="flex items-center justify-between text-xs">
        <div class="flex items-center">
          <div class="w-2 h-2 rounded-full mr-2" 
               :style="{ backgroundColor: item.color }"></div>
          <span class="text-gray-600">{{ item.label }}</span>
        </div>
        <span class="font-medium text-gray-900">{{ item.value }}</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import Chart from 'chart.js/auto'

const props = defineProps({
  title: {
    type: String,
    required: true
  },
  data: {
    type: Array,
    required: true
  }
})

const chartCanvas = ref(null)
let chartInstance = null

const createChart = () => {
  if (chartInstance) {
    chartInstance.destroy()
  }

  if (!props.data || props.data.length === 0) {
    return
  }

  const ctx = chartCanvas.value.getContext('2d')
  
  chartInstance = new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels: props.data.map(item => item.label),
      datasets: [{
        data: props.data.map(item => item.value),
        backgroundColor: props.data.map(item => item.color),
        borderWidth: 0,
        cutout: '60%'
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          display: false
        },
        tooltip: {
          backgroundColor: 'rgba(0, 0, 0, 0.8)',
          titleColor: '#ffffff',
          bodyColor: '#ffffff',
          borderColor: '#3b82f6',
          borderWidth: 1,
          cornerRadius: 8,
          displayColors: true,
          callbacks: {
            label: function(context) {
              const total = context.dataset.data.reduce((a, b) => a + b, 0)
              const percentage = Math.round((context.parsed / total) * 100)
              return `${context.label}: ${context.parsed} (${percentage}%)`
            }
          }
        }
      }
    }
  })
}

onMounted(() => {
  createChart()
})

watch(() => props.data, () => {
  createChart()
}, { deep: true })
</script>
