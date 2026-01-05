<?php

return [
    /*
    |--------------------------------------------------------------------------
    | League Teams
    |--------------------------------------------------------------------------
    |
    | Define the 4 teams that will compete in the Champions League simulation.
    | Each team has a name, logo URL, and team_power rating (0-100).
    | Team power influences match simulation outcomes.
    |
    */

    'teams' => [
        [
            'name' => 'Manchester City',
            'team_power' => 90, // Elite team - favorite to win
            'logo_url' => 'https://resources.premierleague.com/premierleague/badges/50/t43.png',
        ],
        [
            'name' => 'Chelsea',
            'team_power' => 85, // Strong contender
            'logo_url' => 'https://resources.premierleague.com/premierleague/badges/50/t8.png',
        ],
        [
            'name' => 'Liverpool',
            'team_power' => 80, // Good team, can compete
            'logo_url' => 'https://resources.premierleague.com/premierleague/badges/50/t14.png',
        ],
        [
            'name' => 'Arsenal',
            'team_power' => 75, // Underdog, can pull upsets
            'logo_url' => 'https://resources.premierleague.com/premierleague/badges/50/t3.png',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Simulation Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for match simulation algorithm.
    |
    */

    'simulation' => [
        'home_advantage_multiplier' => 1.15, // 15% boost for home team
        'randomness_factor' => 0.3, // 30% variance for unpredictability
        'monte_carlo_simulations' => 10000, // Number of simulations for predictions
    ],

    /*
    |--------------------------------------------------------------------------
    | League Rules
    |--------------------------------------------------------------------------
    |
    | Premier League scoring and standing rules.
    |
    */

    'rules' => [
        'points_for_win' => 3,
        'points_for_draw' => 1,
        'points_for_loss' => 0,
        'total_weeks' => 6, // Round-robin: each team plays others home & away
        'prediction_start_week' => 4, // Predictions available from week 4
    ],
];
