<?php

namespace Database\Seeders;

use App\Models\{Tag, Task, User};
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = Carbon::now()->startOfMonth();
        $lastDay = $now->copy()->endOfMonth()->day - 1;

        $user = User::with('tags')->where('email', 'jean@example.com')->first();
        if ($user === null) {
            $user = User::factory()->create();
        }

        $tags = $user->tags;
        if ($tags->isEmpty()) {
            Tag::factory()->count(5)->create([
                'user_id' => $user->id
            ]);
            $tags = $user->tags;
        }

        for ($i = 1; $i <= 50; $i++) {
            $date = $now->copy()->addDays(rand(0, $lastDay));
            $task = new Task([
                'user_id' => $user->id,
                'date' => $date->toDateString(),
                'contents' => fake()->sentence(),
                'dead_line' => $i%3 === 0 ? $date->hour(rand(10, 22))->toDateTimeString() : null,
                'tag_id' => $i%2 === 0 ? $tags->random()->id : null,
                'done' => (bool)rand(0,1)
            ]);
            $task->save();
        }
    }
}
