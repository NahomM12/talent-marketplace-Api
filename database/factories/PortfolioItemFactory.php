<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\PortfolioItem;
use App\Models\Professional;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PortfolioItem>
 */
class PortfolioItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * service_id is intentionally omitted: the PortfolioItem::saving event
     * copies it from the linked professional so the denormalised copy can
     * never drift.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $mediaType = $this->faker->randomElement(['image', 'youtube', 'pdf']);

        return [
            'title' => $this->faker->catchPhrase(),
            'description' => $this->faker->optional(0.8)->paragraph(3),
            'media_type' => $mediaType,
            'file_path' => $mediaType === 'image'
                ? $this->faker->imageUrl(1280, 720, 'business')
                : ($mediaType === 'pdf' ? 'portfolio/'.$this->faker->uuid().'.pdf' : null),
            'youtube_url' => $mediaType === 'youtube'
                ? 'https://www.youtube.com/watch?v='.$this->faker->regexify('[A-Za-z0-9_-]{11}')
                : null,
            'professional_id' => Professional::query()->inRandomOrder()->value('id'),
            'service_id' => null,
            'is_featured' => $this->faker->boolean(20),
        ];
    }
}
