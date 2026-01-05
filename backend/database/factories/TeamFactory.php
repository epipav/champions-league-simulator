<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class TeamFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->unique()->company().' FC',
            'logo_url' => $this->faker->imageUrl(200, 200, 'sports', true),
            'team_power' => $this->faker->numberBetween(50, 100),
        ];
    }
}
