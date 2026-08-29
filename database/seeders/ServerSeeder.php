<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Server;
use App\Models\Client;
use Illuminate\Support\Str;

class ServerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clients = Client::all();

        foreach ($clients as $client) {
            // Create 2-3 servers per client
            for ($i = 1; $i <= rand(2, 3); $i++) {
                Server::create([
                    'uuid' => Str::uuid(),
                    'client_id' => $client->id,
                    'name' => "{$client->name} - Server {$i}",
                    'ip_address' => '192.168.' . rand(1, 254) . '.' . rand(1, 254),
                    'description' => "Production server for {$client->name}",
                    'tags' => ['production', 'critical', 'web-server'],
                    'is_active' => true,
                ]);
            }
        }
    }
}
