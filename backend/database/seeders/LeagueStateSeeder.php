<?php

namespace Database\Seeders;

use App\Models\LeagueState;
use Illuminate\Database\Seeder;

class LeagueStateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Initialize the league state (week 0, not started)
     *
     * @return void
     */
    public function run()
    {
        LeagueState::create([
            'current_week' => 0,
            'is_completed' => false,
        ]);

        $this->command->info('League state initialized!');
    }
}
