<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\ClientSeeder;
use Database\Seeders\ServerSeeder;
use Database\Seeders\ServerMetricSeeder;
use Database\Seeders\InvoiceSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            ClientSeeder::class,
            ServerSeeder::class,
            ServerMetricSeeder::class,
            InvoiceSeeder::class,
        ]);
    }
}
