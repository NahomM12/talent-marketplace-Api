<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Professional;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Professional>
 */
class ProfessionalFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->unique()->name();

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::lower(Str::random(6)),
            'photo_path' => $this->faker->optional(0.7)->imageUrl(640, 640, 'people'),
            'role_title' => $this->faker->optional(0.9)->jobTitle(),
            'bio' => $this->faker->optional(0.8)->paragraph(3),
            'skills' => $this->faker->randomElements(
                ['Communication', 'Photoshop', 'Premiere Pro', 'SEO', 'CRM', 'Copywriting',
                    'Cold Calling', 'Zendesk', 'Subtitling', 'InDesign', 'Lead Generation',
                    'Calendar Management', 'After Effects', 'Localization', 'Figma', ],
                $this->faker->numberBetween(3, 5),
            ),
            'service_id' => Service::query()->inRandomOrder()->value('id'),
            'status' => $this->faker->boolean(80) ? 'active' : 'inactive',
            'is_featured' => $this->faker->boolean(15),
        ];
    }
}
