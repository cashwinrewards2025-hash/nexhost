<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ServerMetric;
use App\Models\Server;
use Carbon\Carbon;
use Illuminate\Support\Str;

class ServerMetricSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $servers = Server::all();

        foreach ($servers as $server) {
            // Create 288 data points (24 hours at 5-minute intervals)
            for ($i = 288; $i >= 1; $i--) {
                ServerMetric::create([
                    'uuid' => Str::uuid(),
                    'server_id' => $server->id,
                    'cpu_percentage' => rand(10, 85),
                    'memory_percentage' => rand(20, 80),
                    'disk_percentage' => rand(30, 75),
                    'disk_used_gb' => rand(100, 400),
                    'disk_total_gb' => 500,
                    'network_in_bytes' => rand(1000000, 10000000),
                    'network_out_bytes' => rand(1000000, 10000000),
                    'api_response_time_ms' => rand(50, 500),
                    'error_rate_percentage' => rand(0, 5),
                    'load_average' => round(rand(1, 4) + (rand(0, 99) / 100), 2),
                    'status' => 'online',
                    'collected_at' => now()->subMinutes($i * 5),
                ]);
            }
        }
    }
}
