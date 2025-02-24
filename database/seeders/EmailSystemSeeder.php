<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Campaign\Campaign;
use App\Models\EmailList;
use App\Models\EmailListName;
use App\Models\Campaign\EmailHistory;
use App\Models\Campaign\CampaignEmailList;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\DB;

class EmailSystemSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();
        DB::disableQueryLog(); // Disable query log to save memory
        ini_set('memory_limit', '1G'); // Increase memory limit

        // Define lists with their email counts
        $lists = [
            'Customer Database' => 20,
            'Newsletter' => 3,
            'Promotional' => 3
        ];

        // Available servers (from your ServerSeeder)
        $availableServers = [2, 3, 4, 5];

        // Create campaigns first
        $campaigns = [
            [
                'title' => 'Welcome Series',
                'message_id' => 1,
                'is_active' => true,
                'server_id' => array_shift($availableServers)
            ],
            [
                'title' => 'Product Updates',
                'message_id' => 1,
                'is_active' => false,
                'server_id' => array_shift($availableServers)
            ],
            [
                'title' => 'Monthly Newsletter',
                'message_id' => 1,
                'is_active' => true,
                'server_id' => array_shift($availableServers)
            ]
        ];

        $campaignModels = [];
        foreach ($campaigns as $campaign) {
            $campaignModel = Campaign::create([
                'user_id' => 2,
                'title' => $campaign['title'],
                'message_id' => $campaign['message_id'],
                'is_active' => $campaign['is_active']
            ]);

            // Attach single server to campaign
            if (isset($campaign['server_id'])) {
                $campaignModel->servers()->attach($campaign['server_id']);
            }

            $campaignModels[] = $campaignModel;
        }

        // Create lists and emails
        foreach ($lists as $listName => $emailCount) {
            $this->command->info("Creating list: $listName with $emailCount emails");

            $list = EmailListName::create([
                'user_id' => 2,
                'name' => $listName,
            ]);

            // Attach this list to random campaigns using CampaignEmailList
            $randomCampaigns = collect($campaignModels)
                ->random(rand(1, count($campaignModels)))
                ->pluck('id');

            foreach ($randomCampaigns as $campaignId) {
                CampaignEmailList::create([
                    'campaign_id' => $campaignId,
                    'email_list_id' => $list->id
                ]);
            }

            // Create emails in chunks
            $chunkSize = 1000;
            $chunks = ceil($emailCount / $chunkSize);

            for ($i = 0; $i < $chunks; $i++) {
                $this->command->info("Processing chunk " . ($i + 1) . " of $chunks for $listName");

                // Create emails chunk
                $emails = EmailList::factory()
                    ->count(min($chunkSize, $emailCount - ($i * $chunkSize)))
                    ->create([
                        'user_id' => 2,
                        'list_id' => $list->id
                    ]);

                // Create histories for the chunk
                $histories = [];
                foreach ($emails as $email) {
                    $historyCount = rand(0, 3);

                    for ($j = 0; $j < $historyCount; $j++) {
                        $histories[] = [
                            'email_id' => $email->id,
                            'campaign_id' => $faker->randomElement($campaignModels)->id,
                            'status' => $faker->randomElement(['sent', 'failed']),
                            'sent_time' => $faker->dateTimeBetween('-1 month', 'now')->format('Y-m-d H:i:s'),
                        ];
                    }
                }

                // Insert histories in chunks
                foreach (array_chunk($histories, 100) as $historyChunk) {
                    DB::table('email_histories')->insert($historyChunk);
                }

                // Clear memory
                unset($emails, $histories);
                gc_collect_cycles();
            }
        }
    }
}
