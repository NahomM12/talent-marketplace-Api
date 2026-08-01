<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Http\Resources\Concerns\ResolvesPublicStorageUrls;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfessionalResource extends JsonResource
{
    use ResolvesPublicStorageUrls;

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'photo_url' => $this->publicStorageUrl($this->photo_path),
            'role_title' => $this->role_title,
            'bio' => $this->bio,
            'skills' => $this->skills !== null ? array_values((array) $this->skills) : [],
            'service_id' => $this->service_id,
            'status' => $this->status,
            'is_featured' => $this->is_featured,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'service' => ServiceResource::make($this->whenLoaded('service')),
            'portfolio_items' => PortfolioItemResource::collection($this->whenLoaded('portfolioItems')),
        ];
    }
}
