<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\EmailList;
use LucasDotVin\Soulbscription\Models\Plan;

class EmailListSeeder extends Seeder
{
    public function run()
    {
        // Create test users
        $users = User::factory(2)->create();

        foreach ($users as $user) {
            // Create a mix of email statuses for each user

            $user->assignRole('user');
            $user->subscribeTo(Plan::find(1));

            // Failed emails
            EmailList::factory()
                ->failed()
                ->count(10000)
                ->create([
                    'user_id' => $user->id
                ]);

            // Sent emails
            EmailList::factory()
                ->sent()
                ->count(10000)
                ->create([
                    'user_id' => $user->id
                ]);

            // Null status emails
            EmailList::factory()
                ->nullStatus()
                ->count(15)
                ->create([
                    'user_id' => $user->id
                ]);

            // Random status emails
            EmailList::factory()
                ->count(10000)
                ->create([
                    'user_id' => $user->id
                ]);
        }
    }
}
