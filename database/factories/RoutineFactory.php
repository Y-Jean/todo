<?php

namespace Database\Factories;

use App\Models\{Routine, Tag, User};
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Routine>
 */
class RoutineFactory extends Factory
{
    protected $model = Routine::class;

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
            'start_date' => Carbon::now()->addDay()->toDateString(),
            'end_date' => Carbon::now()->addWeek()->toDateString(),
            'type' => fake()->randomElement(['week', 'month']),
            'schedules' => [
                'dates' => [random_int(1, 31)],
                'days_of_week' => [fake()->randomElement(['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'])]
            ],
            'tag_id' => function (array $attributes) {
                return Tag::factory()->create([
                    'user_id' => $attributes['user_id'],
                ])->id;
            },
        ];
    }
}
