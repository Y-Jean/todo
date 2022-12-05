<?php

namespace Database\Seeders;

use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = User::where('email', 'jean@example.com')->first();
        if ($user !==null) {
            Tag::factory()->count(5)->create([
                'user_id' => $user->id
            ]);
        }
    }
}
