<?php

use App\Models\Admin;
use App\Models\PortfolioItem;
use App\Models\Professional;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('manages services and hides inactive service content from public endpoints only', function () {
    $admin = Admin::query()->create([
        'name' => 'Service Admin',
        'email' => 'service-admin@example.com',
        'password' => 'password',
        'role' => Admin::ROLE_ADMIN,
    ]);
    $this->actingAs($admin, 'sanctum');

    $created = $this->postJson('/api/admin/services', [
        'name' => 'Editorial Design',
        'slug' => 'editorial-design',
        'description' => 'Editorial design and production support.',
        'icon' => 'pen-tool',
        'is_active' => false,
        'inclusions' => 'Layout design, Print-ready files, Proofreading',
    ])->assertCreated()
        ->assertJsonPath('data.is_active', false)
        ->assertJsonPath('data.inclusions', ['Layout design', 'Print-ready files', 'Proofreading']);

    $serviceId = $created->json('data.id');

    $this->getJson('/api/admin/services')
        ->assertOk()
        ->assertJsonFragment([
            'id' => $serviceId,
            'is_active' => false,
            'inclusions' => ['Layout design', 'Print-ready files', 'Proofreading'],
        ]);

    $this->getJson('/api/services')->assertOk();
    expect(collect($this->getJson('/api/services')->json('data'))->pluck('id')->all())
        ->not->toContain($serviceId);
    $this->getJson('/api/services/editorial-design')->assertNotFound();

    $professional = Professional::factory()->create([
        'service_id' => $serviceId,
        'status' => 'active',
    ]);
    $portfolioItem = PortfolioItem::factory()->create([
        'professional_id' => $professional->id,
    ]);

    expect(collect($this->getJson('/api/professionals')->json('data'))->pluck('id')->all())
        ->not->toContain($professional->id);
    $this->getJson('/api/professionals/'.$professional->slug)->assertNotFound();

    expect(collect($this->getJson('/api/portfolio')->json('data'))->pluck('id')->all())
        ->not->toContain($portfolioItem->id);
    $this->getJson('/api/portfolio/'.$portfolioItem->id)->assertNotFound();

    $this->getJson('/api/admin/professionals')->assertOk()->assertJsonFragment(['id' => $professional->id]);
    $this->getJson('/api/admin/professionals/'.$professional->id)->assertOk();
    $this->getJson('/api/admin/portfolio')->assertOk()->assertJsonFragment(['id' => $portfolioItem->id]);
    $this->getJson('/api/admin/portfolio/'.$portfolioItem->id)->assertOk();

    $this->putJson('/api/admin/services/'.$serviceId, ['is_active' => true])
        ->assertOk()
        ->assertJsonPath('data.is_active', true);

    expect(collect($this->getJson('/api/services')->json('data'))->pluck('id')->all())
        ->toContain($serviceId);
    $this->getJson('/api/services/editorial-design')->assertOk();
    expect(collect($this->getJson('/api/professionals')->json('data'))->pluck('id')->all())
        ->toContain($professional->id);
    $this->getJson('/api/professionals/'.$professional->slug)->assertOk();
    expect(collect($this->getJson('/api/portfolio')->json('data'))->pluck('id')->all())
        ->toContain($portfolioItem->id);
    $this->getJson('/api/portfolio/'.$portfolioItem->id)->assertOk();

    $this->putJson('/api/admin/services/'.$serviceId, [
        'name' => 'Editorial and Publication Design',
        'slug' => 'editorial-publication-design',
        'inclusions' => 'Page layout, Print production',
    ])->assertOk()
        ->assertJsonPath('data.slug', 'editorial-publication-design')
        ->assertJsonPath('data.inclusions', ['Page layout', 'Print production']);

    $this->deleteJson('/api/admin/services/'.$serviceId)->assertNoContent();
    $this->getJson('/api/admin/services')->assertOk()->assertJsonMissing(['id' => $serviceId]);
});
