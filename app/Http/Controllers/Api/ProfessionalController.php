<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProfessionalListResource;
use App\Http\Resources\ProfessionalResource;
use App\Models\Professional;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProfessionalController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Professional::query()
            ->where('status', 'active')
            ->with('service')
            ->orderBy('name');

        $serviceSlug = $request->query('service');
        if (is_string($serviceSlug) && $serviceSlug !== '') {
            $query->whereHas('service', function ($serviceQuery) use ($serviceSlug): void {
                $serviceQuery->where('slug', $serviceSlug);
            });
        }

        return ProfessionalListResource::collection($query->get());
    }

    public function show(Professional $professional): ProfessionalResource
    {
        if ($professional->status !== 'active') {
            abort(404);
        }

        $professional->load([
            'service',
            'portfolioItems.professional',
            'portfolioItems.service',
        ]);

        return new ProfessionalResource($professional);
    }
}
