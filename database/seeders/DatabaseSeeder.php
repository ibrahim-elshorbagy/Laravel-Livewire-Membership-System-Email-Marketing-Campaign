<?php

namespace Database\Seeders;

use App\Models\PlayGround\Todo;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(PlansSeeder::class);
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $userRole = Role::firstOrCreate(['name' => 'user']);

        User::factory(50)->create()->each(function ($user) use ($userRole) {
            $user->assignRole($userRole);
        });




         $user = User::factory()->create([
            'first_name' => 'ibrahim',
            'last_name' => 'elshorbagy',
            'username' => 'a',
            'email' => 'a@a.a',
            'password' => bcrypt('a'),
            'image_url'=>'https://cdn-icons-png.flaticon.com/512/3135/3135715.png',
            'active' => true
        ]);
        $user->assignRole($adminRole);

        Todo::create([
            'user_id' => 1,
            'title' => 'first todo',
            'description' => 'first todo description',
            'image_url' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRSjmY6j4zBSeKxLjTXNj4oK2g4xrtAj9rTNw&s'
        ]);
    }
}
