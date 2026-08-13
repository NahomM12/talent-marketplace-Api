<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Resources\AdminResource;
use App\Models\Admin;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        // The 'sanctum' guard (driver: sanctum) is a RequestGuard — built to
        // RESOLVE an already-authenticated request from a token, not to
        // perform a credentials-based login. It has no attempt()/logout()
        // support, so credential verification is done manually here instead.
        $admin = Admin::where('email', $request->string('email'))->first();

        if (! $admin || ! Hash::check($request->string('password'), $admin->password)) {
            return new JsonResponse(
                ['message' => 'Invalid credentials.'],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

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
            // request was authenticated via Bearer token — the only auth
            // method this app uses now. Guard against anything else defensively.
            if ($currentToken instanceof PersonalAccessToken) {
                $currentToken->delete();
            }
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