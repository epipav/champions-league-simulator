<template>
  <div class="min-h-screen bg-gray-100 py-8">
    <div class="container mx-auto px-4">
      <!-- Header -->
      <header class="text-center mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Champions League Simulator</h1>
        <p v-if="store.isInitialized" class="text-gray-600 mt-2">
          Week {{ Math.min(store.currentWeek + 1, 6) }} of 6
          <span v-if="store.isCompleted" class="text-green-600 font-medium">
            - Season Complete!
          </span>
        </p>
      </header>

      <!-- Error Alert -->
      <div
        v-if="store.error"
        class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6"
      >
        {{ store.error }}
      </div>

      <!-- Loading State -->
      <div
        v-if="store.loading && !initialLoad"
        class="fixed inset-0 bg-black bg-opacity-30 flex items-center justify-center z-50"
      >
        <div class="bg-white rounded-lg p-6 shadow-xl">
          <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-blue-600 mx-auto"></div>
          <p class="mt-4 text-gray-600">Running simulation...</p>
        </div>
      </div>

      <!-- Not Initialized State -->
      <div v-if="!store.isInitialized && !initialLoad" class="text-center py-8">
        <div class="bg-white rounded-lg shadow p-8 max-w-md mx-auto">
          <!-- Champions League Logo -->
          <img
            src="./assets/UEFA_Champions_League.svg.png"
            alt="UEFA Champions League"
            class="w-32 h-32 mx-auto mb-6 object-contain"
          />

          <h2 class="text-2xl font-bold text-gray-800 mb-4">Welcome!</h2>
          <p class="text-gray-600 mb-6">
            Start a new Champions League season with 4 teams competing in a round-robin tournament.
          </p>

          <!-- Anthem Lyrics -->
          <div class="bg-gray-100 text-gray-600 rounded-lg p-4 mb-6">
            <div class="space-y-1 italic">
              <p>Ils sont les meilleurs</p>
              <p>Sie sind die Besten</p>
              <p class="font-semibold text-gray-800">These are the champions</p>
            </div>
          </div>

          <ResetButton
            :loading="store.loading"
            :is-initialized="store.isInitialized"
            @initialize="store.initializeLeague"
            @reset="store.fullResetLeague"
          />
        </div>
      </div>

      <!-- Main Content - 3 Column Layout (only when initialized) -->
      <div
        v-else-if="store.isInitialized"
        class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-stretch"
      >
        <!-- Left Column: League Table -->
        <div class="flex flex-col">
          <LeagueTable
            :standings="store.standings"
            :can-play-all="store.canPlayNextWeek"
            :loading="store.loading"
            class="flex-1"
            @play-all="store.playAllWeeks"
          />
        </div>

        <!-- Center Column: Match Results -->
        <div class="flex flex-col">
          <MatchResults
            :matches="store.matches"
            :current-week="store.currentWeek"
            :can-play-next="store.canPlayNextWeek"
            :loading="store.loading"
            class="flex-1"
            @play-week="store.playNextWeek"
            @update-score="handleUpdateScore"
          />
        </div>

        <!-- Right Column: Predictions + Reset Button -->
        <div class="flex flex-col">
          <Predictions
            :predictions="store.predictions"
            :current-week="store.currentWeek"
            class="flex-1"
          />

          <!-- Reset Button -->
          <div class="mt-4 text-center">
            <ResetButton
              :loading="store.loading"
              :is-initialized="store.isInitialized"
              @initialize="store.initializeLeague"
              @reset="store.fullResetLeague"
            />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useLeagueStore } from './stores/league'
import LeagueTable from './components/LeagueTable.vue'
import MatchResults from './components/MatchResults.vue'
import Predictions from './components/Predictions.vue'
import ResetButton from './components/ResetButton.vue'

const store = useLeagueStore()
const initialLoad = ref<boolean>(true)

onMounted(async () => {
  await store.fetchAll()
  initialLoad.value = false
})

const handleUpdateScore = async (
  matchId: number,
  homeScore: number,
  awayScore: number
): Promise<void> => {
  await store.updateMatchScore(matchId, homeScore, awayScore)
}
</script>
