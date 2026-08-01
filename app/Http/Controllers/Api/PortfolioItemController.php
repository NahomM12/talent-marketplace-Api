<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PortfolioItemResource;
use App\Models\PortfolioItem;
use App\Services\PortfolioFilterService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PortfolioItemController extends Controller
{
    public function __construct(
        private readonly PortfolioFilterService $portfolioFilterService,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $portfolioItems = $this->portfolioFilterService->paginateFiltered($request);

        return PortfolioItemResource::collection($portfolioItems);
    }

    public function show(PortfolioItem $portfolioItem): PortfolioItemResource
    {
        $portfolioItem->load(['professional', 'service']);

        return new PortfolioItemResource($portfolioItem);
    }
}
