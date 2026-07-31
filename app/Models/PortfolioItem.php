<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\PortfolioItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['title', 'description', 'media_type', 'file_path', 'youtube_url', 'professional_id', 'service_id', 'is_featured'])]
#[UseFactory(PortfolioItemFactory::class)]
class PortfolioItem extends Model
{
    /** @use HasFactory<PortfolioItemFactory> */
    use HasFactory;

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
        ];
    }

    /**
     * Keep the denormalised service_id in sync with the linked professional's
     * service. Per the architecture doc, portfolio_items.service_id is a copy
     * of the professional's service_id to avoid a join on every filtered
     * request, so it must never drift.
     */
    protected static function booted(): void
    {
        static::saving(function (PortfolioItem $portfolioItem): void {
            $professionalServiceId = $portfolioItem->professional
                ?->service_id
                ?? Professional::query()
                    ->whereKey($portfolioItem->professional_id)
                    ->value('service_id');

            if ($professionalServiceId !== null) {
                $portfolioItem->service_id = $professionalServiceId;
            }
        });
    }

    /**
     * Professional who produced this portfolio item.
     *
     * @return BelongsTo<Professional>
     */
    public function professional(): BelongsTo
    {
        return $this->belongsTo(Professional::class);
    }

    /**
     * Service this portfolio item is categorised under (denormalised copy).
     *
     * @return BelongsTo<Service>
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
