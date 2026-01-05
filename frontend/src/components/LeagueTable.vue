<template>
  <div class="bg-white rounded-lg shadow p-4 flex flex-col h-full">
    <h2 class="text-xl font-bold mb-4 text-gray-800">League Table</h2>

    <div class="flex-1">
      <table class="w-full text-sm">
        <thead>
          <tr class="border-b-2 border-gray-200">
            <th class="text-left py-2 px-1">Teams</th>
            <th class="text-center py-2 px-1">PTS</th>
            <th class="text-center py-2 px-1">P</th>
            <th class="text-center py-2 px-1">W</th>
            <th class="text-center py-2 px-1">D</th>
            <th class="text-center py-2 px-1">L</th>
            <th class="text-center py-2 px-1">GD</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="(standing, index) in standings"
            :key="standing.team.id"
            :class="['border-b border-gray-100', index === 0 ? 'bg-green-50' : '']"
          >
            <td class="py-2 px-1">
              <div class="flex items-center gap-2">
                <img
                  v-if="standing.team.logo_url"
                  :src="standing.team.logo_url"
                  :alt="standing.team.name"
                  class="w-5 h-5 object-contain"
                />
                <span class="font-medium">{{ standing.team.name }}</span>
              </div>
            </td>
            <td class="text-center py-2 px-1 font-bold">{{ standing.points }}</td>
            <td class="text-center py-2 px-1">{{ standing.played }}</td>
            <td class="text-center py-2 px-1">{{ standing.won }}</td>
            <td class="text-center py-2 px-1">{{ standing.drawn }}</td>
            <td class="text-center py-2 px-1">{{ standing.lost }}</td>
            <td class="text-center py-2 px-1" :class="gdClass(standing.goal_difference)">
              {{ formatGD(standing.goal_difference) }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="mt-4 flex gap-2">
      <button
        @click="$emit('playAll')"
        :disabled="!canPlayAll || loading"
        class="flex-1 bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed transition"
      >
        {{ loading ? 'Playing...' : 'Play All' }}
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { Standing } from '@/types'

interface Props {
  standings: Standing[]
  canPlayAll?: boolean
  loading?: boolean
}

withDefaults(defineProps<Props>(), {
  canPlayAll: true,
  loading: false,
})

defineEmits<{
  playAll: []
}>()

const formatGD = (gd: number): string => {
  if (gd > 0) return `+${gd}`
  return gd.toString()
}

const gdClass = (gd: number): string => {
  if (gd > 0) return 'text-green-600'
  if (gd < 0) return 'text-red-600'
  return ''
}
</script>
