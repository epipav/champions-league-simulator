<template>
  <div class="bg-white rounded-lg shadow p-4 flex flex-col h-full">
    <h2 class="text-xl font-bold mb-4 text-gray-800">
      {{ currentWeek }}{{ ordinalSuffix(currentWeek) }} Week Predictions of Championship
    </h2>

    <div v-if="predictions.length > 0" class="space-y-3 flex-1">
      <div
        v-for="prediction in predictions"
        :key="prediction.team.id"
        class="flex items-center gap-3"
      >
        <img
          v-if="prediction.team.logo_url"
          :src="prediction.team.logo_url"
          :alt="prediction.team.name"
          class="w-6 h-6 object-contain"
        />
        <span class="flex-1 font-medium">{{ prediction.team.name }}</span>
        <div class="w-32 bg-gray-200 rounded-full h-4 overflow-hidden">
          <div
            class="h-full bg-blue-600 transition-all duration-500"
            :style="{ width: prediction.probability + '%' }"
          ></div>
        </div>
        <span class="font-bold text-blue-600 w-12 text-right"> %{{ prediction.probability }} </span>
      </div>
    </div>

    <div v-else class="text-gray-500 text-center py-8 flex-1 flex flex-col justify-center">
      <p>Predictions will be available from Week 4</p>
      <p class="text-sm mt-2">Play more matches to see championship predictions</p>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { Prediction } from '@/types'

interface Props {
  predictions: Prediction[]
  currentWeek?: number
}

withDefaults(defineProps<Props>(), {
  currentWeek: 0,
})

const ordinalSuffix = (n: number): string => {
  const s = ['th', 'st', 'nd', 'rd']
  const v = n % 100
  return s[(v - 20) % 10] || s[v] || s[0]
}
</script>
