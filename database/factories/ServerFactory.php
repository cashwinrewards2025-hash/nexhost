<?php

namespace Database\Factories;

use App\Models\Server;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Server>
 */
class ServerFactory extends Factory
{
    protected $model = Server::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'uuid' => Str::uuid(),
            'name' => $this->faker->word() . '-server-' . rand(1, 100),
            'ip_address' => $this->faker->ipv4(),
            'description' => $this->faker->sentence(),
            'tags' => ['production', 'web-server'],
            'is_active' => true,
            'is_demo' => false,
        ];
    }
}
