<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class FootballMatchFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'week' => $this->faker->numberBetween(1, 6),
            'is_played' => false,
            'home_score' => null,
            'away_score' => null,
        ];
    }

    /**
     * Indicate that the match has been played.
     */
    public function played()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_played' => true,
                'home_score' => $this->faker->numberBetween(0, 5),
                'away_score' => $this->faker->numberBetween(0, 5),
            ];
        });
    }
}
