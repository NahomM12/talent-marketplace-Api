<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Http\Resources\Concerns\ResolvesPublicStorageUrls;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfessionalListResource extends JsonResource
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
            'role_title' => $this->role_title,
            'photo_url' => $this->publicStorageUrl($this->photo_path),
            'service_name' => $this->when(
                $this->relationLoaded('service') && $this->service !== null,
                fn (): string => $this->service->name,
            ),
            'is_featured' => $this->is_featured,
        ];
    }
}
