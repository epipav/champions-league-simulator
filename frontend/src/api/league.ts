import axios, { type AxiosInstance } from 'axios'
import type {
  Team,
  Standing,
  Match,
  Prediction,
  LeagueState,
  MessageResponse,
  PlayWeekResponse,
  PlayAllWeekItem,
  InitializeResponse,
} from '@/types'

const api: AxiosInstance = axios.create({
  baseURL: '/api/v1',
  headers: {
    'Content-Type': 'application/json',
  },
})

export const leagueApi = {
  // Teams
  async getTeams(): Promise<Team[]> {
    const response = await api.get<Team[]>('/teams')
    return response.data
  },

  // League
  async getStandings(): Promise<Standing[]> {
    const response = await api.get<Standing[]>('/league/standings')
    return response.data
  },

  async getLeagueState(): Promise<LeagueState> {
    const response = await api.get<LeagueState>('/league/state')
    return response.data
  },

  // Matches
  async getAllMatches(): Promise<Match[]> {
    const response = await api.get<Match[]>('/matches')
    return response.data
  },

  async getMatchesByWeek(week: number): Promise<Match[]> {
    const response = await api.get<Match[]>(`/matches/week/${week}`)
    return response.data
  },

  async playNextWeek(): Promise<PlayWeekResponse> {
    const response = await api.post<PlayWeekResponse>('/matches/play-week')
    return response.data
  },

  async playAll(): Promise<PlayAllWeekItem[]> {
    const response = await api.post<PlayAllWeekItem[]>('/matches/play-all')
    return response.data
  },

  async resetLeague(): Promise<MessageResponse> {
    const response = await api.post<MessageResponse>('/matches/reset')
    return response.data
  },

  async initializeLeague(): Promise<InitializeResponse> {
    const response = await api.post<InitializeResponse>('/league/initialize')
    return response.data
  },

  async fullResetLeague(): Promise<InitializeResponse> {
    const response = await api.post<InitializeResponse>('/league/full-reset')
    return response.data
  },

  async updateMatch(matchId: number, homeScore: number, awayScore: number): Promise<Match> {
    const response = await api.put<Match>(`/matches/${matchId}`, {
      home_score: homeScore,
      away_score: awayScore,
    })
    return response.data
  },

  // Predictions
  async getPredictions(): Promise<Prediction[]> {
    const response = await api.get<Prediction[]>('/predictions')
    return response.data
  },
}
