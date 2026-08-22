<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProfessionalRequest;
use App\Http\Requests\UpdateProfessionalRequest;
use App\Http\Resources\ProfessionalListResource;
use App\Http\Resources\ProfessionalResource;
use App\Models\Professional;
use App\Services\RevalidationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class ProfessionalController extends Controller
{
    private const string PHOTO_DISK = 'public';
    private const string PHOTO_DIR = 'professionals';

    public function __construct(
        private readonly RevalidationService $revalidation,
    ) {}

    public function index(): AnonymousResourceCollection
    {
        // Admin sees every professional, including inactive ones (unlike the public API).
        return ProfessionalListResource::collection(
            Professional::query()->with('service')->orderBy('id')->get()
        );
    }
public function show(int $id): ProfessionalResource
{
    $professional = Professional::with(['service', 'portfolioItems'])->findOrFail($id);

    return new ProfessionalResource($professional);
}
    public function store(StoreProfessionalRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['slug'] = $this->uniqueSlug($data['name']);

        if ($request->hasFile('photo')) {
            $data['photo_path'] = $this->storePhoto($request->file('photo'));
        }

        /** @var Professional $professional */
        $professional = Professional::query()->create($data);
        $professional->load('service');

        $this->revalidation->notify(['talent', 'talent-'.$professional->slug]);

        return (new ProfessionalResource($professional))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function update(UpdateProfessionalRequest $request, int $id): ProfessionalResource
    {
        $professional = Professional::query()->findOrFail($id);

        $data = $request->validated();

        // Slugs are never updated (not in the request rules); public URLs stay stable.

        if ($request->hasFile('photo')) {
            $data['photo_path'] = $this->replacePhoto(
                $request->file('photo'),
                $professional->photo_path
            );
        }

        $professional->update($data);
        $professional->load('service');

        $this->revalidation->notify(['talent', 'talent-'.$professional->slug]);

        return new ProfessionalResource($professional);
    }

    public function destroy(int $id): JsonResponse
    {
        $professional = Professional::query()->with('portfolioItems')->findOrFail($id);
        $slug = $professional->slug;

        // DB cascades the portfolio_items rows; clean up their files so they don't orphan.
        foreach ($professional->portfolioItems as $portfolioItem) {
            $this->deleteFile($portfolioItem->file_path);
        }
        $this->deleteFile($professional->photo_path);

        $professional->delete();

        $this->revalidation->notify(['talent', 'talent-'.$slug]);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Generate a unique slug from the name. A short random suffix is appended on collision.
     */
    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;

        while (Professional::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.Str::lower(Str::random(6));
        }

        return $slug;
    }

    private function storePhoto(UploadedFile $file): string
    {
        return $file->store(self::PHOTO_DIR, self::PHOTO_DISK);
    }

    private function replacePhoto(UploadedFile $file, ?string $oldPath): string
    {
        $newPath = $this->storePhoto($file);
        $this->deleteFile($oldPath);

        return $newPath;
    }

    private function deleteFile(?string $path): void
    {
        if ($path !== null && $path !== '' && Storage::disk(self::PHOTO_DISK)->exists($path)) {
            Storage::disk(self::PHOTO_DISK)->delete($path);
        }
    }
}
