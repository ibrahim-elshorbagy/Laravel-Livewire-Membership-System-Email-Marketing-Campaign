<?php

namespace Database\Seeders;

use App\Models\PlayGround\Todo;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'ibrahim elshorbagy',
            'email' => 'a@a.a',
            'password' => bcrypt('a'),
        ]);

        Todo::create([
            'user_id' => 1,
            'title' => 'first todo',
            'description' => 'first todo description',
            'image_url' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRSjmY6j4zBSeKxLjTXNj4oK2g4xrtAj9rTNw&s'
        ]);
    }
}
