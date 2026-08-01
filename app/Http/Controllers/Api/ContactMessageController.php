<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreContactMessageRequest;
use App\Models\ContactMessage;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ContactMessageController extends Controller
{
    public function store(StoreContactMessageRequest $request): JsonResponse
    {
        ContactMessage::query()->create($request->validated());

        return new JsonResponse(
            ['message' => 'Your message has been received. We will get back to you soon.'],
            Response::HTTP_CREATED
        );
    }
}
