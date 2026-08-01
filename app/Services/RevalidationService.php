<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RevalidationService
{
    /**
     * Notify the Next.js frontend to invalidate the given ISR tags.
     *
     * Failures are logged but never thrown: a dead or not-yet-built revalidation
     * endpoint must never break an admin's save action.
     *
     * @param  array<int, string>  $tags
     */
    public function notify(array $tags): void
    {
        $url = config('services.revalidation.url');
        $secret = config('services.revalidation.secret');

        if (! is_string($url) || $url === '') {
            Log::debug('Revalidation skipped: NEXTJS_REVALIDATE_URL is not configured.', [
                'tags' => $tags,
            ]);

            return;
        }

        try {
            Http::withToken((string) $secret)
                ->timeout(10)
                ->post($url, ['tags' => $tags]);
        } catch (\Throwable $e) {
            Log::error('Next.js revalidation webhook failed.', [
                'url' => $url,
                'tags' => $tags,
                'exception' => $e->getMessage(),
            ]);
        }
    }
}
