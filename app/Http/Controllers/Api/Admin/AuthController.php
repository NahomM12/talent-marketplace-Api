<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Resources\AdminResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
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

        // Also issue a personal access token for the frontend's token-based
        // admin auth flow (browser cross-origin cookie auth isn't viable here
        // — see gm-bridge-technical-architecture.md's admin auth note). The
        // session-based login above is left intact as a fallback/rollback path.
        $token = $admin->createToken('admin-panel')->plainTextToken;

        return new JsonResponse([
            'admin' => new AdminResource($admin),
            'token' => $token,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user) {
            $currentToken = $user->currentAccessToken();

            // currentAccessToken() returns a real PersonalAccessToken when the
            // request was authenticated via Bearer token, or a TransientToken
            // (no delete() support) when authenticated via session — only
            // revoke the former.
            if ($currentToken instanceof PersonalAccessToken) {
                $currentToken->delete();
            }
        }

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