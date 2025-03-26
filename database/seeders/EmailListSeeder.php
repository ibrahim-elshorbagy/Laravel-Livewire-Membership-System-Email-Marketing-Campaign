<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\EmailList;
use App\Models\EmailListName;
use LucasDotVin\Soulbscription\Models\Plan;

class EmailListSeeder extends Seeder
{
    public function run()
    {
        // Create test users
        $users = User::factory(1)->create();

        foreach ($users as $user) {
            $user->assignRole('user');
            $user->graceSubscribeTo(Plan::find(1));

            // Create lists for the user
            $lists = [
                // 'Marketing Campaigns' => 15000,
                // 'Newsletter Subscribers' => 10000,
                'Customer Database' => 2,
                'Test' => 5,
                'Big Test' => 4,


            ];

            foreach ($lists as $listName => $emailCount) {
                $list = EmailListName::create([
                    'user_id' => $user->id,
                    'name' => $listName,
                ]);

                // Distribution of email statuses

                // Failed emails
                EmailList::factory()
                    ->count($emailCount)
                    ->create([
                        'user_id' => $user->id,
                        'list_id' => $list->id
                    ]);

            }
        }
    }
}
