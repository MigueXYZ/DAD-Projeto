<?php
// database/factories/GameFactory.php

namespace Database\Factories;

use App\Models\Game;
use App\Models\User;
use App\Models\Board;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
* @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Game>
*/
class GameFactory extends Factory
{
    /**
    * Define the model's default state.
    *
    * @return array<string, mixed>
    */
    public function definition(): array
    {
        return [
            'created_user_id' => User::factory(), // Creating a random user for the creator
            'winner_user_id' => User::factory(),  // Creating a random user for the winner
            'type' => $this->faker->randomElement(['S', 'M']), // Random type
            'status' => $this->faker->randomElement(['PE', 'PL', 'E','I']), // Random status
            'began_at' => $this->faker->dateTimeThisYear(), // Random began date within the year
            'ended_at' => $this->faker->dateTimeBetween($startDate = 'now', $endDate = '+1 week'), // Random end date
            'total_time' => $this->faker->randomFloat(2, 0, 120), // Random total time between 0 and 120 minutes
            'board_id' => Board::factory(), // Creating a random board
            'custom' => $this->faker->word, // Random custom data as string
        ];
    }
}
