<?php

namespace Database\Seeders;

use App\Models\Server;
use Illuminate\Database\Seeder;

class ServerSeeder extends Seeder
{
    public function run(): void
    {
        $servers = [
            [
                'name' => 'Production Server 1',
                'assigned_to_user_id' => 2,
                'last_access_time' => now(),
                'current_quota' => 75,
                'admin_notes' => 'Main production server for user 2',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Development Server 1',
                'assigned_to_user_id' => 2,
                'last_access_time' => now()->subDays(2),
                'current_quota' => 45,
                'admin_notes' => 'Development environment for user 2',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Testing Server 1',
                'assigned_to_user_id' => 2,
                'last_access_time' => now()->subDays(1),
                'current_quota' => 60,
                'admin_notes' => 'Testing environment for user 2',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Backup Server 1',
                'assigned_to_user_id' => 2,
                'last_access_time' => now()->subHours(12),
                'current_quota' => 30,
                'admin_notes' => 'Backup server for user 2',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($servers as $server) {
            Server::create($server);
        }
    }
}
