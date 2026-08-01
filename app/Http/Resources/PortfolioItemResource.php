<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Http\Resources\Concerns\ResolvesPublicStorageUrls;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PortfolioItemResource extends JsonResource
{
    use ResolvesPublicStorageUrls;

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'media_type' => $this->media_type,
            'media_url' => $this->resolveMediaUrl(),
            'professional' => $this->when(
                $this->relationLoaded('professional') && $this->professional !== null,
                fn (): array => [
                    'name' => $this->professional->name,
                    'slug' => $this->professional->slug,
                ],
            ),
            'service_name' => $this->when(
                $this->relationLoaded('service') && $this->service !== null,
                fn (): string => $this->service->name,
            ),
            'is_featured' => $this->is_featured,
        ];
    }

    private function resolveMediaUrl(): ?string
    {
        return match ($this->media_type) {
            'youtube' => $this->youtube_url,
            'image', 'pdf' => $this->publicStorageUrl($this->file_path),
            default => null,
        };
    }
}
