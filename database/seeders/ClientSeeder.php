<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Client;
use Illuminate\Support\Str;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clients = [
            [
                'name' => 'Acme Corporation',
                'email' => 'admin@acme.com',
                'billing_email' => 'billing@acme.com',
                'phone' => '+91-98765-43210',
                'company_name' => 'Acme Corporation Ltd.',
                'address' => '123 Business Street',
                'city' => 'Mumbai',
                'state' => 'Maharashtra',
                'postal_code' => '400001',
                'country' => 'India',
                'tax_id' => 'GST27AABCT1234H1Z0',
            ],
            [
                'name' => 'Tech Solutions Inc',
                'email' => 'contact@techsolutions.com',
                'billing_email' => 'finance@techsolutions.com',
                'phone' => '+91-87654-32109',
                'company_name' => 'Tech Solutions Inc.',
                'address' => '456 Innovation Avenue',
                'city' => 'Bangalore',
                'state' => 'Karnataka',
                'postal_code' => '560001',
                'country' => 'India',
                'tax_id' => 'GST29AABCU5432K2Z1',
            ],
            [
                'name' => 'Global Enterprises',
                'email' => 'info@globalenterprises.com',
                'billing_email' => 'accounting@globalenterprises.com',
                'phone' => '+91-76543-21098',
                'company_name' => 'Global Enterprises Pvt Ltd',
                'address' => '789 Corporate Plaza',
                'city' => 'Delhi',
                'state' => 'Delhi',
                'postal_code' => '110001',
                'country' => 'India',
                'tax_id' => 'GST07AABCL9876M1Z2',
            ],
        ];

        foreach ($clients as $client) {
            Client::create(array_merge($client, [
                'uuid' => Str::uuid(),
                'is_demo' => false,
            ]));
        }
    }
}
