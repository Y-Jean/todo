<?php

namespace Database\Factories;

use App\Models\{Tag, Task, User};
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    protected $model = Task::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'user_id' => User::factory()->create()->id,
            'contents' => fake()->sentence(),
            'date' => Carbon::now()->toDateString(),
            'tag_id' => function (array $attributes) {
                return Tag::factory()->create([
                    'user_id' => $attributes['user_id'],
                ])->id;
            },
        ];
    }
}
