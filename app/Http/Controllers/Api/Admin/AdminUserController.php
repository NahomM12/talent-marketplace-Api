<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAdminUserRequest;
use App\Http\Requests\UpdateAdminUserRequest;
use App\Http\Resources\AdminResource;
use App\Models\Admin;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class AdminUserController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return AdminResource::collection(
            Admin::query()->orderBy('id')->get()
        );
    }

    public function store(StoreAdminUserRequest $request): JsonResponse
    {
        $admin = Admin::query()->create($request->validated());

        return (new AdminResource($admin))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function update(UpdateAdminUserRequest $request, int $id): AdminResource
    {
        $admin = Admin::query()->findOrFail($id);

        $data = $request->validated();
        // Don't overwrite an existing password with null on a partial update.
        if (array_key_exists('password', $data) && $data['password'] === null) {
            unset($data['password']);
        }

        $admin->update($data);

        return new AdminResource($admin);
    }

    public function destroy(int $id, JsonResponse $response): JsonResponse
    {
        /** @var Admin $actor */
        $actor = request()->user();

        if ((int) $actor->getkey() === $id) {
            return new JsonResponse(
                ['message' => 'You cannot delete your own account.'],
                Response::HTTP_FORBIDDEN
            );
        }

        $admin = Admin::query()->findOrFail($id);
        $admin->delete();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
