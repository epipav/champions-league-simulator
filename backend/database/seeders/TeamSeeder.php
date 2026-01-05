<?php

namespace Database\Seeders;

use App\Models\Team;
use Illuminate\Database\Seeder;

class TeamSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Seed the 4 teams from config/league.php
     *
     * @return void
     */
    public function run()
    {
        $teams = config('league.teams');

        foreach ($teams as $teamData) {
            Team::create([
                'name' => $teamData['name'],
                'team_power' => $teamData['team_power'],
                'logo_url' => $teamData['logo_url'],
            ]);
        }

        $this->command->info('Teams seeded successfully!');
    }
}
