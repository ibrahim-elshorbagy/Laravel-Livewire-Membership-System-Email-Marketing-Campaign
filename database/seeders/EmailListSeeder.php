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
            $user->subscribeTo(Plan::find(1));

            // Create lists for the user
            $lists = [
                'Marketing Campaigns' => 15000,
                'Newsletter Subscribers' => 10000,
                'Customer Database' => 5000,
                // 'Big Test' => 100000,


            ];

            foreach ($lists as $listName => $emailCount) {
                $list = EmailListName::create([
                    'user_id' => $user->id,
                    'name' => $listName,
                ]);

                // Distribution of email statuses
                $failedCount = (int)($emailCount * 0.3); // 30% failed
                $sentCount = (int)($emailCount * 0.4);   // 40% sent
                $nullCount = (int)($emailCount * 0.2);   // 20% null
                $randomCount = $emailCount - ($failedCount + $sentCount + $nullCount); // remaining random

                // Failed emails
                EmailList::factory()
                    ->failed()
                    ->count($failedCount)
                    ->create([
                        'user_id' => $user->id,
                        'list_id' => $list->id
                    ]);

                // Sent emails
                EmailList::factory()
                    ->sent()
                    ->count($sentCount)
                    ->create([
                        'user_id' => $user->id,
                        'list_id' => $list->id
                    ]);

                // Null status emails
                EmailList::factory()
                    ->nullStatus()
                    ->count($nullCount)
                    ->create([
                        'user_id' => $user->id,
                        'list_id' => $list->id
                    ]);

                // Random status emails
                EmailList::factory()
                    ->count($randomCount)
                    ->create([
                        'user_id' => $user->id,
                        'list_id' => $list->id
                    ]);
            }
        }
    }
}
