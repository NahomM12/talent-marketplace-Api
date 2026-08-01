<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSuperadmin
{
    public function handle(Request $request, \Closure $next): Response
    {
        $user = $request->user();

        if (! $user instanceof \App\Models\Admin) {
            return new JsonResponse(['message' => 'Unauthenticated.'], Response::HTTP_UNAUTHORIZED);
        }

        if (! $user->isSuperadmin()) {
            return new JsonResponse(['message' => 'Forbidden. Superadmin access required.'], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
