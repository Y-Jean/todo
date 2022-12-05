<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tag>
 */
class TagFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $user_id = User::factory()->create()->id;
        static $position = 0;
        return [
            'user_id' => $user_id,
            'name' => fake()->word(),
            'color' => fake()->hexColor(),
            'position' => $position++
        ];
    }
}
