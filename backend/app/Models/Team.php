<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Team extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'logo_url',
        'team_power',
    ];

    protected $casts = [
        'team_power' => 'integer',
    ];

    /**
     * Get the matches where this team is the home team.
     */
    public function homeMatches(): HasMany
    {
        return $this->hasMany(FootballMatch::class, 'home_team_id');
    }

    /**
     * Get the matches where this team is the away team.
     */
    public function awayMatches(): HasMany
    {
        return $this->hasMany(FootballMatch::class, 'away_team_id');
    }

    /**
     * Get the predictions for this team.
     */
    public function predictions(): HasMany
    {
        return $this->hasMany(Prediction::class);
    }
}
