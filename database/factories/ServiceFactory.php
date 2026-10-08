<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Service>
 */
class ServiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->unique()->words(2, true);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => $this->faker->sentence(12),
            'type' => $this->faker->randomElement(['remote_talent', 'managed_services']),
            'is_active' => true,
            'inclusions' => $this->faker->randomElements(
                ['Planning and consultation', 'Dedicated project support', 'Quality review', 'Final deliverables'],
                $this->faker->numberBetween(3, 4),
            ),
        ];
    }
}
