<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreServiceRequest;
use App\Http\Requests\UpdateServiceRequest;
use App\Http\Resources\ServiceResource;
use App\Models\Service;
use App\Services\RevalidationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class ServiceController extends Controller
{
    public function __construct(
        private readonly RevalidationService $revalidation,
    ) {}

    public function index(): AnonymousResourceCollection
    {
        return ServiceResource::collection(Service::query()->orderBy('name')->get());
    }

    public function store(StoreServiceRequest $request): JsonResponse
    {
        /** @var Service $service */
        $service = Service::query()->create($request->validated());

        $this->revalidation->notify(['services', 'service-'.$service->slug]);

        return (new ServiceResource($service))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function update(UpdateServiceRequest $request, int $id): ServiceResource
    {
        $service = Service::query()->findOrFail($id);
        $oldSlug = $service->slug;
        $service->update($request->validated());

        $this->revalidation->notify(['services', 'service-'.$oldSlug, 'service-'.$service->slug]);

        return new ServiceResource($service);
    }

    public function destroy(int $id): JsonResponse
    {
        $service = Service::query()->findOrFail($id);
        $slug = $service->slug;
        $service->delete();

        $this->revalidation->notify(['services', 'service-'.$slug]);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
