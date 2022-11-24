<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        User::factory()->count(10)->create();

        User::factory()->create([
            'name' => 'jean',
            'email' => 'test@example.com',
        ]);

        if (env('APP_ENV') === 'local' || env('APP_ENV') === 'testing') {
            $this->call([
                TagSeeder::class,
            ]);
        }
    }
}
