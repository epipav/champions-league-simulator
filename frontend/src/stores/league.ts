import { defineStore } from 'pinia'
import { leagueApi } from '@/api/league'
import type { Team, Standing, Match, Prediction } from '@/types'

interface LeagueState {
  teams: Team[]
  standings: Standing[]
  matches: Match[]
  predictions: Prediction[]
  currentWeek: number
  isCompleted: boolean
  isInitialized: boolean
  loading: boolean
  error: string | null
}

interface MatchesByWeek {
  [week: number]: Match[]
}

export const useLeagueStore = defineStore('league', {
  state: (): LeagueState => ({
    teams: [],
    standings: [],
    matches: [],
    predictions: [],
    currentWeek: 0,
    isCompleted: false,
    isInitialized: false,
    loading: false,
    error: null,
  }),

  getters: {
    matchesByWeek: (state): MatchesByWeek => {
      const grouped: MatchesByWeek = {}
      for (let week = 1; week <= 6; week++) {
        grouped[week] = state.matches.filter((m) => m.week === week)
      }
      return grouped
    },

    playedWeeks: (state): number[] => {
      return state.matches
        .filter((m) => m.is_played)
        .map((m) => m.week)
        .filter((v, i, a) => a.indexOf(v) === i)
        .sort((a, b) => a - b)
    },

    canPlayNextWeek: (state): boolean => {
      return state.currentWeek < 6
    },

    showPredictions: (state): boolean => {
      return state.currentWeek >= 4
    },
  },

  actions: {
    async fetchAll(): Promise<void> {
      this.loading = true
      this.error = null
      try {
        await Promise.all([this.fetchStandings(), this.fetchMatches(), this.fetchLeagueState()])
        if (this.currentWeek >= 4) {
          await this.fetchPredictions()
        }
      } catch (err) {
        this.error = err instanceof Error ? err.message : 'Failed to fetch data'
      } finally {
        this.loading = false
      }
    },

    async fetchTeams(): Promise<void> {
      this.teams = await leagueApi.getTeams()
    },

    async fetchStandings(): Promise<void> {
      this.standings = await leagueApi.getStandings()
    },

    async fetchMatches(): Promise<void> {
      this.matches = await leagueApi.getAllMatches()
    },

    async fetchLeagueState(): Promise<void> {
      try {
        const state = await leagueApi.getLeagueState()
        this.currentWeek = state.current_week
        this.isCompleted = state.is_completed
        this.isInitialized = true
      } catch {
        this.currentWeek = 0
        this.isCompleted = false
        this.isInitialized = false
      }
    },

    async fetchPredictions(): Promise<void> {
      try {
        this.predictions = await leagueApi.getPredictions()
      } catch {
        this.predictions = []
      }
    },

    async playNextWeek(): Promise<void> {
      this.loading = true
      this.error = null
      try {
        await leagueApi.playNextWeek()
        await this.fetchAll()
      } catch (err) {
        this.error = (err as any).response?.data?.message || 'Failed to play week'
      } finally {
        this.loading = false
      }
    },

    async playAllWeeks(): Promise<void> {
      this.loading = true
      this.error = null
      try {
        await leagueApi.playAll()
        await this.fetchAll()
      } catch (err) {
        this.error = (err as any).response?.data?.message || 'Failed to play all weeks'
      } finally {
        this.loading = false
      }
    },

    async resetLeague(): Promise<void> {
      this.loading = true
      this.error = null
      try {
        await leagueApi.resetLeague()
        this.predictions = []
        await this.fetchAll()
      } catch (err) {
        this.error = (err as any).response?.data?.message || 'Failed to reset league'
      } finally {
        this.loading = false
      }
    },

    async initializeLeague(): Promise<void> {
      this.loading = true
      this.error = null
      try {
        await leagueApi.initializeLeague()
        this.isInitialized = true
        await this.fetchAll()
      } catch (err) {
        this.error = (err as any).response?.data?.message || 'Failed to initialize league'
      } finally {
        this.loading = false
      }
    },

    async fullResetLeague(): Promise<void> {
      this.loading = true
      this.error = null
      try {
        await leagueApi.fullResetLeague()
        this.predictions = []
        this.isInitialized = true
        await this.fetchAll()
      } catch (err) {
        this.error = (err as any).response?.data?.message || 'Failed to reset league'
      } finally {
        this.loading = false
      }
    },

    async updateMatchScore(
      matchId: number,
      homeScore: number,
      awayScore: number
    ): Promise<void> {
      this.loading = true
      this.error = null
      try {
        await leagueApi.updateMatch(matchId, homeScore, awayScore)
        await this.fetchAll()
      } catch (err) {
        this.error = (err as any).response?.data?.message || 'Failed to update match'
      } finally {
        this.loading = false
      }
    },
  },
})
