<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ServiceResource;
use App\Models\Service;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ServiceController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $services = Service::query()->where('is_active', true)->orderBy('name')->get();

        return ServiceResource::collection($services);
    }

    public function show(Service $service): ServiceResource
    {
        if ($service->is_active === false) {
            abort(404);
        }

        return new ServiceResource($service);
    }
}
