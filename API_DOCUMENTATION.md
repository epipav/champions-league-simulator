# Champions League API Documentation

Base URL: `http://localhost:8000/api/v1`

## Endpoints

### Teams

#### GET `/teams`
Get all teams in the league.

**Response (200 OK):**
```json
[
  {
    "id": 1,
    "name": "Manchester City",
    "logo_url": "https://...",
    "team_power": 90,
    "created_at": "...",
    "updated_at": "..."
  }
]
```

#### GET `/teams/{id}`
Get a specific team with its current standing.

**Response (200 OK):**
```json
{
  "team": {
    "id": 1,
    "name": "Manchester City",
    "logo_url": "https://...",
    "team_power": 90
  },
  "standing": {
    "position": 1,
    "played": 6,
    "won": 4,
    "drawn": 1,
    "lost": 1,
    "goals_for": 12,
    "goals_against": 6,
    "goal_difference": 6,
    "points": 13
  }
}
```

**Error (404 Not Found):**
```json
{
  "message": "Team not found"
}
```

---

### League

#### GET `/league/standings`
Get current league standings sorted by Premier League rules (Points > GD > Goals For > Name).

**Response (200 OK):**
```json
[
  {
    "position": 1,
    "played": 6,
    "won": 3,
    "drawn": 2,
    "lost": 1,
    "goals_for": 8,
    "goals_against": 3,
    "goal_difference": 5,
    "points": 11,
    "team": {
      "id": 13,
      "name": "Liverpool",
      "logo_url": "https://...",
      "team_power": 80
    }
  }
]
```

#### GET `/league/state`
Get current league state (which week we're on).

**Response (200 OK):**
```json
{
  "current_week": 3,
  "is_completed": false
}
```

**Error (404 Not Found):**
```json
{
  "message": "League not initialized"
}
```

#### POST `/league/initialize`
Initialize the league with 4 teams and generate fixtures.

**Response (200 OK):**
```json
{
  "teams": 4,
  "fixtures": 12,
  "message": "League initialized successfully"
}
```

**Error (400 Bad Request):**
```json
{
  "message": "League already initialized. Use reset to start over."
}
```

#### POST `/league/full-reset`
Delete all data and reinitialize the league from scratch.

**Response (200 OK):**
```json
{
  "teams": 4,
  "fixtures": 12,
  "message": "League fully reset and reinitialized"
}
```

---

### Matches

#### GET `/matches`
Get all matches (played and unplayed).

**Response (200 OK):**
```json
[
  {
    "id": 1,
    "home_team_id": 1,
    "away_team_id": 2,
    "home_score": 2,
    "away_score": 1,
    "week": 1,
    "is_played": true,
    "created_at": "...",
    "updated_at": "...",
    "home_team": { "id": 1, "name": "Manchester City", ... },
    "away_team": { "id": 2, "name": "Chelsea", ... }
  }
]
```

#### GET `/matches/week/{week}`
Get matches for a specific week (1-6).

**Parameters:**
- `week` (path parameter): Week number (1-6)

**Response (200 OK):**
```json
[
  {
    "id": 1,
    "home_team_id": 1,
    "away_team_id": 2,
    "home_score": 2,
    "away_score": 1,
    "week": 1,
    "is_played": true,
    "home_team": { ... },
    "away_team": { ... }
  }
]
```

**Error (400 Bad Request):**
```json
{
  "message": "Week must be between 1 and 6"
}
```

#### POST `/matches/play-week`
Simulate and play all matches for the next week.

**Response (200 OK):**
```json
{
  "week": 1,
  "matches": [
    {
      "id": 1,
      "home_score": 2,
      "away_score": 1,
      "is_played": true,
      "home_team": { ... },
      "away_team": { ... }
    }
  ],
  "message": "Week 1 matches played successfully"
}
```

**Error (400 Bad Request):**
```json
{
  "message": "All weeks have been played"
}
```

**Error (404 Not Found):**
```json
{
  "message": "League not initialized"
}
```

#### POST `/matches/play-all`
Simulate and play all remaining weeks at once.

**Response (200 OK):**
```json
[
  {
    "week": 2,
    "matches": [ ... ]
  },
  {
    "week": 3,
    "matches": [ ... ]
  }
]
```

**Error (400 Bad Request):**
```json
{
  "message": "All weeks have already been played"
}
```

**Error (404 Not Found):**
```json
{
  "message": "League not initialized"
}
```

#### PUT `/matches/{id}`
Update a match score manually.

**Parameters:**
- `id` (path parameter): Match ID

**Request Body:**
```json
{
  "home_score": 2,
  "away_score": 1
}
```

**Validation:**
- `home_score`: Required, integer, min 0, max 99
- `away_score`: Required, integer, min 0, max 99

**Response (200 OK):**
```json
{
  "id": 1,
  "home_team_id": 1,
  "away_team_id": 2,
  "home_score": 2,
  "away_score": 1,
  "week": 1,
  "is_played": true,
  "home_team": { ... },
  "away_team": { ... }
}
```

**Error (404 Not Found):**
```json
{
  "message": "Match not found"
}
```

**Error (422 Unprocessable Entity):**
```json
{
  "message": "The home score field is required. (and 1 more error)",
  "errors": {
    "home_score": ["The home score field is required."],
    "away_score": ["The away score must be at least 0."]
  }
}
```

#### POST `/matches/reset`
Reset the league (for testing). Marks all matches as unplayed and resets league state to week 0.

**Response (200 OK):**
```json
{
  "message": "League reset successfully"
}
```

---

### Predictions

#### GET `/predictions`
Get championship win probability predictions for each team. Only available from week 4 onwards.

**Response (200 OK):**
```json
[
  {
    "team": {
      "id": 1,
      "name": "Manchester City",
      "logo_url": "https://..."
    },
    "probability": 45.5
  },
  {
    "team": {
      "id": 2,
      "name": "Liverpool",
      "logo_url": "https://..."
    },
    "probability": 30.2
  }
]
```

**Error (400 Bad Request):**
```json
{
  "message": "Predictions are only available from week 4 onwards"
}
```

---

## Match Simulation Algorithm

The match simulation uses the following algorithm:

1. **Team Power**: Each team has a base power rating (60-90)
2. **Home Advantage**: Home team gets +15% power boost
3. **Randomness**: Each team's power gets ±30% random variance
4. **Expected Goals**: Calculated using power ratio: `(teamPower / opponentPower) * 1.5`
5. **Actual Score**: Generated using Poisson distribution based on expected goals

This creates realistic football results where:
- Stronger teams usually win (>70% against weaker opponents)
- Upsets can happen (weaker teams win 5-40% depending on power difference)
- Home advantage matters
- Scores are realistic (average 2-4 goals per match)

---

## Example Usage

### Complete workflow:

```bash
# 1. Check initial state
curl http://localhost:8000/api/v1/league/state
# Response: {"current_week": 0}

# 2. View teams
curl http://localhost:8000/api/v1/teams

# 3. View week 1 fixtures
curl http://localhost:8000/api/v1/matches/week/1

# 4. Play week 1
curl -X POST http://localhost:8000/api/v1/matches/play-week

# 5. Check standings after week 1
curl http://localhost:8000/api/v1/league/standings

# 6. Play all remaining weeks
curl -X POST http://localhost:8000/api/v1/matches/play-all

# 7. View final standings
curl http://localhost:8000/api/v1/league/standings

# 8. Reset for new simulation
curl -X POST http://localhost:8000/api/v1/matches/reset
```

---

## Response Structure

### Success Responses

All successful requests return data directly in the response body with a `200 OK` status code:

- **Single resources**: Return the object directly
- **Collections**: Return an array directly
- **Operations with messages**: Return an object with the relevant data and a `message` field

### Error Responses

All error responses include a `message` field describing the error:

```json
{
  "message": "Error description here"
}
```

For validation errors (422 Unprocessable Entity), the response also includes detailed error information:

```json
{
  "message": "Summary of validation errors",
  "errors": {
    "field_name": ["Error message for this field"]
  }
}
```

Common HTTP status codes:
- `200 OK`: Successful request
- `400 Bad Request`: Invalid input (e.g., week out of range, predictions before week 4)
- `404 Not Found`: Resource not found (e.g., match not found, league not initialized)
- `422 Unprocessable Entity`: Validation errors (e.g., negative scores, missing required fields)
