<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ContactMessageResource;
use App\Models\ContactMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class ContactMessageController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return ContactMessageResource::collection(
            ContactMessage::query()
                ->orderByDesc('id')
                ->paginate(10)
                ->withQueryString()
        );
    }

    public function show(int $id): ContactMessageResource
    {
        $contactMessage = ContactMessage::query()->findOrFail($id);

        if ($contactMessage->read_at === null) {
            $contactMessage->update(['read_at' => now()]);
        }

        return new ContactMessageResource($contactMessage);
    }

    public function destroy(int $id): JsonResponse
    {
        ContactMessage::query()->findOrFail($id)->delete();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}