<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\PortfolioItem;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class PortfolioFilterService
{
    public function paginateFiltered(Request $request, int $perPage = 10): LengthAwarePaginator
    {
        $query = PortfolioItem::query()
            ->with(['professional', 'service'])
            ->orderByDesc('id');

        $serviceSlug = $request->query('service');
        if (is_string($serviceSlug) && $serviceSlug !== '') {
            $query->whereHas('service', function ($serviceQuery) use ($serviceSlug): void {
                $serviceQuery->where('slug', $serviceSlug);
            });
        }

        $search = $request->query('search');
        if (is_string($search) && $search !== '') {
            $query->where('title', 'like', '%'.addcslashes($search, '%_\\').'%');
        }

        return $query->paginate($perPage)->withQueryString();
    }
}
