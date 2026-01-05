// Base Team type
export interface Team {
  id: number
  name: string
  logo_url: string
  team_power: number
  created_at?: string
  updated_at?: string
}

// Standing with team information
export interface Standing {
  position: number
  played: number
  won: number
  drawn: number
  lost: number
  goals_for: number
  goals_against: number
  goal_difference: number
  points: number
  team: Team
}

// Match type
export interface Match {
  id: number
  home_team_id: number
  away_team_id: number
  home_score: number
  away_score: number
  week: number
  is_played: boolean
  created_at?: string
  updated_at?: string
  home_team: Team
  away_team: Team
}

// Prediction type
export interface Prediction {
  team: Team
  probability: number
}

// League state
export interface LeagueState {
  current_week: number
  is_completed: boolean
}

// Response with message
export interface MessageResponse {
  message: string
}

// Play week response
export interface PlayWeekResponse {
  week: number
  matches: Match[]
  message: string
}

// Play all week item
export interface PlayAllWeekItem {
  week: number
  matches: Match[]
}

// Initialize/Reset response
export interface InitializeResponse {
  teams: number
  fixtures: number
  message: string
}
