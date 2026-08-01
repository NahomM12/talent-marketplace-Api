<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\PortfolioItem;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class UpdatePortfolioItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * service_id is not accepted from the client — the controller copies it from
     * the linked professional when persisting the portfolio item.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'media_type' => ['sometimes', 'required', 'string', Rule::in(['image', 'youtube', 'pdf'])],
            'professional_id' => ['sometimes', 'required', 'integer', Rule::exists('professionals', 'id')],
            'is_featured' => ['sometimes', 'required', 'boolean'],
            'image' => [
                'nullable',
                'file',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
                'prohibited_unless:media_type,image',
            ],
            'pdf' => [
                'nullable',
                'file',
                'mimes:pdf',
                'max:20480',
                'prohibited_unless:media_type,pdf',
            ],
            'youtube_url' => [
                'nullable',
                'url',
                'regex:/^https?:\/\/(www\.)?(youtube\.com\/(watch\?v=|embed\/|shorts\/)|youtu\.be\/)[\w-]+/i',
                'prohibited_unless:media_type,youtube',
            ],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $mediaType = $this->effectiveMediaType();
            $existing = $this->existingPortfolioItem();

            if ($mediaType === 'image' && ! $this->hasFile('image')) {
                $hasExistingImage = $existing !== null
                    && $existing->media_type === 'image'
                    && filled($existing->file_path);

                if (! $hasExistingImage) {
                    $validator->errors()->add('image', 'An image file is required when media type is image.');
                }
            }

            if ($mediaType === 'pdf' && ! $this->hasFile('pdf')) {
                $hasExistingPdf = $existing !== null
                    && $existing->media_type === 'pdf'
                    && filled($existing->file_path);

                if (! $hasExistingPdf) {
                    $validator->errors()->add('pdf', 'A PDF file is required when media type is pdf.');
                }
            }

            if ($mediaType === 'youtube' && ! $this->filled('youtube_url')) {
                $hasExistingYoutube = $existing !== null
                    && $existing->media_type === 'youtube'
                    && filled($existing->youtube_url);

                if (! $hasExistingYoutube) {
                    $validator->errors()->add('youtube_url', 'A YouTube URL is required when media type is youtube.');
                }
            }
        });
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            new JsonResponse(
                ['message' => 'Validation failed.', 'errors' => $validator->errors()],
                JsonResponse::HTTP_UNPROCESSABLE_ENTITY
            )
        );
    }

    private function effectiveMediaType(): string
    {
        if ($this->filled('media_type')) {
            return (string) $this->input('media_type');
        }

        return $this->existingPortfolioItem()?->media_type ?? '';
    }

    private function existingPortfolioItem(): ?PortfolioItem
    {
        $routeParam = $this->route('portfolioItem') ?? $this->route('id');

        if ($routeParam instanceof PortfolioItem) {
            return $routeParam;
        }

        if (is_numeric($routeParam)) {
            return PortfolioItem::query()->find((int) $routeParam);
        }

        return null;
    }
}
