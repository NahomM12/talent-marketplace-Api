<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Resources\AdminResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    public function login(LoginRequest $request): JsonResponse|AdminResource
    {
        // sanctum guard uses session + admins provider; attempt() is the idiomatic login path for SPA cookie auth.
        if (! Auth::guard('sanctum')->attempt($request->only('email', 'password'))) {
            return new JsonResponse(
                ['message' => 'Invalid credentials.'],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        if ($request->hasSession()) {
            $request->session()->regenerate();
        }

        /** @var \App\Models\Admin $admin */
        $admin = Auth::guard('sanctum')->user();

        return new AdminResource($admin);
    }

    public function logout(Request $request): JsonResponse
    {
        Auth::guard('sanctum')->logout();

        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    public function me(Request $request): AdminResource
    {
        /** @var \App\Models\Admin $admin */
        $admin = $request->user();

        return new AdminResource($admin);
    }
}
