<?php

namespace Database\Factories;

use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Client>
 */
class ClientFactory extends Factory
{
    protected $model = Client::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'uuid' => Str::uuid(),
            'name' => $this->faker->company(),
            'email' => $this->faker->companyEmail(),
            'billing_email' => $this->faker->companyEmail(),
            'phone' => $this->faker->phoneNumber(),
            'company_name' => $this->faker->company(),
            'address' => $this->faker->streetAddress(),
            'city' => $this->faker->city(),
            'state' => $this->faker->state(),
            'postal_code' => $this->faker->postcode(),
            'country' => 'India',
            'tax_id' => 'GST' . str_pad(rand(1, 99), 2, '0', STR_PAD_LEFT) . Str::random(14),
            'total_paid' => 0,
            'total_due' => 0,
            'is_active' => true,
            'is_demo' => false,
        ];
    }
}
