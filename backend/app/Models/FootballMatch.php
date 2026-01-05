<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FootballMatch extends Model
{
    use HasFactory;

    protected $table = 'matches';

    protected $fillable = [
        'home_team_id',
        'away_team_id',
        'home_score',
        'away_score',
        'week',
        'is_played',
    ];

    protected $casts = [
        'home_team_id' => 'integer',
        'away_team_id' => 'integer',
        'home_score' => 'integer',
        'away_score' => 'integer',
        'week' => 'integer',
        'is_played' => 'boolean',
    ];

    /**
     * Get the home team.
     */
    public function homeTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'home_team_id');
    }

    /**
     * Get the away team.
     */
    public function awayTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'away_team_id');
    }
}
