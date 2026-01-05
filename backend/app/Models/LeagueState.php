<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeagueState extends Model
{
    use HasFactory;

    protected $table = 'league_state';

    protected $fillable = [
        'current_week',
        'is_completed',
    ];

    protected $casts = [
        'current_week' => 'integer',
        'is_completed' => 'boolean',
    ];
}
