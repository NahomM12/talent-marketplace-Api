<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePortfolioItemRequest;
use App\Http\Requests\UpdatePortfolioItemRequest;
use App\Http\Resources\PortfolioItemResource;
use App\Models\PortfolioItem;
use App\Services\RevalidationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class PortfolioItemController extends Controller
{
    private const string MEDIA_DISK = 'public';
    private const string MEDIA_DIR = 'portfolio';

    public function __construct(
        private readonly RevalidationService $revalidation,
    ) {}

    public function index(): AnonymousResourceCollection
    {
        return PortfolioItemResource::collection(
            PortfolioItem::query()
                ->with(['professional', 'service'])
                ->orderByDesc('id')
                ->get()
        );
    }

    public function store(StorePortfolioItemRequest $request): JsonResponse
    {
        $data = $request->safe()->except(['image', 'pdf', 'youtube_url']);
        $data = array_merge($data, $this->resolveMedia($request->media_type, $request));

        /** @var PortfolioItem $portfolioItem */
        $portfolioItem = PortfolioItem::query()->create($data);
        $portfolioItem->load(['professional', 'service']);

        $this->revalidation->notify(['portfolio']);

        return (new PortfolioItemResource($portfolioItem))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function update(UpdatePortfolioItemRequest $request, int $id): PortfolioItemResource
    {
        $portfolioItem = PortfolioItem::query()->findOrFail($id);

        $data = $request->safe()->except(['image', 'pdf', 'youtube_url']);

        // The effective media type governs cleanup: an explicit value in the
        // request wins, otherwise the record's existing type carries over.
        $mediaType = $request->filled('media_type')
            ? $request->string('media_type')->toString()
            : $portfolioItem->media_type;

        $data = array_merge($data, $this->resolveMedia($mediaType, $request, $portfolioItem));

        $portfolioItem->update($data);
        $portfolioItem->load(['professional', 'service']);

        $this->revalidation->notify(['portfolio']);

        return new PortfolioItemResource($portfolioItem);
    }

    public function destroy(int $id): JsonResponse
    {
        $portfolioItem = PortfolioItem::query()->findOrFail($id);

        $this->deleteFile($portfolioItem->file_path);
        $portfolioItem->delete();

        $this->revalidation->notify(['portfolio']);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Map the request onto file_path / youtube_url according to the media type.
     *
     * For image/pdf uploads any superseded stored file is removed; for youtube
     * uploads a prior file (from a media-type switch) is cleaned up too. The
     * PortfolioItem::saving() hook re-derives service_id — it is never set here.
     *
     * @return array<string, string|null>
     */
    private function resolveMedia(string $mediaType, $request, ?PortfolioItem $existing = null): array
    {
        return match ($mediaType) {
            'image' => [
                'media_type' => 'image',
                'file_path' => $this->resolveUpload(
                    $request->file('image'),
                    $existing->file_path ?? null,
                    in_array($existing->media_type ?? null, ['image', 'pdf'], true)
                ),
                'youtube_url' => $this->clearingYoutube($existing),
            ],
            'pdf' => [
                'media_type' => 'pdf',
                'file_path' => $this->resolveUpload(
                    $request->file('pdf'),
                    $existing->file_path ?? null,
                    in_array($existing->media_type ?? null, ['image', 'pdf'], true)
                ),
                'youtube_url' => $this->clearingYoutube($existing),
            ],
            'youtube' => [
                'media_type' => 'youtube',
                'file_path' => $this->switchingFromUpload($existing),
                'youtube_url' => $this->resolveYoutube(
                    $request->filled('youtube_url') ? $request->string('youtube_url')->toString() : null,
                    $existing->youtube_url ?? null,
                ),
            ],
            default => [],
        };
    }

    /**
     * Store a new upload when present; otherwise keep the existing file.
     * Replaces the old file only when it belongs to a compatible (file-based) media type.
     */
    private function resolveUpload(?UploadedFile $upload, ?string $existingPath, bool $existingWasFile): ?string
    {
        if ($upload !== null) {
            $newPath = $upload->store(self::MEDIA_DIR, self::MEDIA_DISK);
            if ($existingWasFile) {
                $this->deleteFile($existingPath);
            }

            return $newPath;
        }

        return $existingPath;
    }

    /**
     * New URL wins; otherwise keep the existing one. (YouTube URLs are not "files".)
     */
    private function resolveYoutube(?string $newUrl, ?string $existingUrl): ?string
    {
        return $newUrl ?? $existingUrl;
    }

    /**
     * When the media type is switching to youtube from an image/pdf, drop the old file.
     */
    private function switchingFromUpload(?PortfolioItem $existing): ?string
    {
        if (
            $existing !== null
            && in_array($existing->media_type, ['image', 'pdf'], true)
            && filled($existing->file_path)
        ) {
            $this->deleteFile($existing->file_path);
        }

        return null;
    }

    private function clearingYoutube(?PortfolioItem $existing): ?string
    {
        if ($existing !== null && $existing->media_type === 'youtube') {
            return null;
        }

        return $existing?->youtube_url;
    }

    private function deleteFile(?string $path): void
    {
        if ($path !== null && $path !== '' && Storage::disk(self::MEDIA_DISK)->exists($path)) {
            Storage::disk(self::MEDIA_DISK)->delete($path);
        }
    }
}
