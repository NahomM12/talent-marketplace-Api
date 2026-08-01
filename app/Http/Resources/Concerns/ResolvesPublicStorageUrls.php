<?php

declare(strict_types=1);

namespace App\Http\Resources\Concerns;

use Illuminate\Support\Facades\Storage;

trait ResolvesPublicStorageUrls
{
    protected function publicStorageUrl(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return Storage::disk('public')->url($path);
    }
}
