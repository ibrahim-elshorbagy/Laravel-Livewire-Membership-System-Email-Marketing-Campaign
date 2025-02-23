<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use LucasDotVin\Soulbscription\Models\Plan;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(PlansSeeder::class);
        $this->call(SettingSeeder::class);

        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $userRole = Role::firstOrCreate(['name' => 'user']);

        // User::factory(10000)->create()->each(function ($user) use ($userRole) {
        //     $user->assignRole($userRole);
        // });




         $user = User::factory()->create([
            'first_name' => 'Administrator',
            'last_name' => 'admin',
            'username' => 'a',
            'email' => 'a@a.a',
            'password' => bcrypt('a'),
            'image_url'=>'https://cdn-icons-png.flaticon.com/512/3135/3135715.png',
            'active' => true
        ]);
        $user->assignRole($adminRole);

        $user = User::factory()->create([
            'first_name' => 'ibrahim',
            'last_name' => 'elshorbagy',
            'username' => 'u',
            'email' => 'ibrahim.elshorbagy47@gmail.com',
            'password' => bcrypt('u'),
            'image_url'=>'https://cdn-icons-png.flaticon.com/512/3135/3135715.png',
            'active' => true
        ]);
        $user->assignRole($userRole);
        $user->subscribeTo(Plan::find(1));

        $this->call(EmailMessageSeeder::class);
        $this->call(ServerSeeder::class);


        // $this->call([EmailListSeeder::class]);
        $this->call([EmailSystemSeeder::class]);

    }
}
