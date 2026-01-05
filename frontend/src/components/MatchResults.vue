<template>
  <div class="bg-white rounded-lg shadow p-4 flex flex-col h-full">
    <h2 class="text-xl font-bold mb-4 text-gray-800">
      Match Results
      <span class="text-sm font-normal text-gray-500"> (Week {{ displayWeek }}) </span>
    </h2>

    <!-- Week selector tabs -->
    <div class="flex gap-1 mb-4 flex-wrap">
      <button
        v-for="week in 6"
        :key="week"
        @click="selectedWeek = week"
        :class="[
          'px-3 py-1 rounded text-sm transition',
          selectedWeek === week
            ? 'bg-blue-600 text-white'
            : week <= currentWeek
              ? 'bg-gray-200 hover:bg-gray-300'
              : 'bg-gray-100 text-gray-400',
        ]"
      >
        W{{ week }}
      </button>
    </div>

    <!-- Matches for selected week -->
    <div class="space-y-3 flex-1">
      <div v-for="match in weekMatches" :key="match.id" class="border border-gray-200 rounded p-3">
        <div class="flex items-center justify-between">
          <!-- Home Team -->
          <div class="flex items-center gap-2 flex-1">
            <img
              v-if="match.home_team.logo_url"
              :src="match.home_team.logo_url"
              :alt="match.home_team.name"
              class="w-6 h-6 object-contain"
            />
            <span class="font-medium text-sm">{{ match.home_team.name }}</span>
          </div>

          <!-- Score -->
          <div class="flex items-center gap-2 mx-4">
            <template v-if="match.is_played">
              <input
                v-if="editingMatch === match.id"
                type="number"
                v-model.number="editHomeScore"
                min="0"
                max="99"
                class="w-12 text-center border rounded py-1 text-lg font-bold"
              />
              <span
                v-else
                @click="startEditing(match)"
                class="text-xl font-bold cursor-pointer hover:text-blue-600"
                title="Click to edit"
              >
                {{ match.home_score }}
              </span>

              <span class="text-gray-400">-</span>

              <input
                v-if="editingMatch === match.id"
                type="number"
                v-model.number="editAwayScore"
                min="0"
                max="99"
                class="w-12 text-center border rounded py-1 text-lg font-bold"
              />
              <span
                v-else
                @click="startEditing(match)"
                class="text-xl font-bold cursor-pointer hover:text-blue-600"
                title="Click to edit"
              >
                {{ match.away_score }}
              </span>
            </template>
            <span v-else class="text-gray-400 text-lg">vs</span>
          </div>

          <!-- Away Team -->
          <div class="flex items-center gap-2 flex-1 justify-end">
            <span class="font-medium text-sm">{{ match.away_team.name }}</span>
            <img
              v-if="match.away_team.logo_url"
              :src="match.away_team.logo_url"
              :alt="match.away_team.name"
              class="w-6 h-6 object-contain"
            />
          </div>
        </div>

        <!-- Edit buttons -->
        <div v-if="editingMatch === match.id" class="flex gap-2 mt-2 justify-center">
          <button
            @click="saveEdit(match.id)"
            class="bg-green-600 text-white px-3 py-1 rounded text-sm hover:bg-green-700"
          >
            Save
          </button>
          <button
            @click="cancelEdit"
            class="bg-gray-400 text-white px-3 py-1 rounded text-sm hover:bg-gray-500"
          >
            Cancel
          </button>
        </div>
      </div>

      <div v-if="weekMatches.length === 0" class="text-gray-500 text-center py-4">
        No matches for this week
      </div>
    </div>

    <!-- Next Week Button -->
    <div class="mt-4">
      <button
        @click="$emit('playWeek')"
        :disabled="!canPlayNext || loading"
        class="w-full bg-green-600 text-white py-2 px-4 rounded hover:bg-green-700 disabled:bg-gray-400 disabled:cursor-not-allowed transition"
      >
        {{ playButtonText }}
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, type Ref, type ComputedRef } from 'vue'
import type { Match } from '@/types'

interface Props {
  matches: Match[]
  currentWeek?: number
  canPlayNext?: boolean
  loading?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  currentWeek: 0,
  canPlayNext: true,
  loading: false,
})

const emit = defineEmits<{
  playWeek: []
  updateScore: [matchId: number, homeScore: number, awayScore: number]
}>()

const selectedWeek: Ref<number> = ref(1)
const editingMatch: Ref<number | null> = ref(null)
const editHomeScore: Ref<number> = ref(0)
const editAwayScore: Ref<number> = ref(0)

// Auto-select current week or latest played
watch(
  () => props.currentWeek,
  (newWeek: number) => {
    if (newWeek > 0) {
      selectedWeek.value = newWeek
    }
  },
  { immediate: true }
)

const displayWeek: ComputedRef<number> = computed(() => {
  return selectedWeek.value
})

const weekMatches: ComputedRef<Match[]> = computed(() => {
  return props.matches.filter((m) => m.week === selectedWeek.value)
})

const nextWeekToPlay: ComputedRef<number> = computed(() => {
  // Cap at week 6 (max weeks)
  return Math.min(props.currentWeek + 1, 6)
})

const playButtonText: ComputedRef<string> = computed(() => {
  if (props.loading) return 'Playing...'
  return `Play matches in Week ${nextWeekToPlay.value}`
})

const startEditing = (match: Match): void => {
  editingMatch.value = match.id
  editHomeScore.value = match.home_score
  editAwayScore.value = match.away_score
}

const cancelEdit = (): void => {
  editingMatch.value = null
}

const saveEdit = (matchId: number): void => {
  emit('updateScore', matchId, editHomeScore.value, editAwayScore.value)
  editingMatch.value = null
}
</script>
