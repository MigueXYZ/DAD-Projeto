<?php
// database/factories/BoardFactory.php

namespace Database\Factories;

use App\Models\Board;
use Illuminate\Database\Eloquent\Factories\Factory;

class BoardFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'board_cols' => $this->faker->numberBetween(5, 20),
            'board_rows' => $this->faker->numberBetween(5, 20),
            'custom' => $this->faker->words(3, true)
        ];
    }
}
