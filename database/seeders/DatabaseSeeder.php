<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\PortfolioItem;
use App\Models\Professional;
use App\Models\Service;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Model events are deliberately NOT suppressed: PortfolioItem's saving
     * event auto-syncs service_id from the linked professional, and we want
     * that contract enforced on seeded data too.
     */
    public function run(): void
    {
        $this->call(ServiceSeeder::class);

        // 8 professionals spread deliberately across the 6 services (2,2,1,1,1,1)
        // so every service has talent to build against.
        foreach (['graphic-design', 'video-editing'] as $serviceSlug) {
            Professional::factory(2)->create([
                'service_id' => $this->serviceIdFor($serviceSlug),
            ]);
        }

        foreach (['virtual-assistance', 'sdr-bdr', 'customer-support', 'translation'] as $serviceSlug) {
            Professional::factory()->create([
                'service_id' => $this->serviceIdFor($serviceSlug),
            ]);
        }

        // Mark exactly two professionals featured, deterministically by id.
        Professional::query()->update(['is_featured' => false]);
        Professional::query()
            ->orderBy('id')
            ->limit(2)
            ->update(['is_featured' => true]);

        // ~20 portfolio items spread across the professionals (2-3 each).
        // service_id is filled by the model's saving event.
        PortfolioItem::factory(20)->create();

        // Mark exactly four portfolio items featured, deterministically.
        PortfolioItem::query()->update(['is_featured' => false]);
        PortfolioItem::query()
            ->orderBy('id')
            ->limit(4)
            ->update(['is_featured' => true]);
    }

    /**
     * Resolve a service slug to its id.
     */
    private function serviceIdFor(string $slug): int
    {
        return (int) Service::query()->where('slug', $slug)->value('id');
    }
}
