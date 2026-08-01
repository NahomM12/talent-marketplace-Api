<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class StorePortfolioItemRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'media_type' => ['required', 'string', Rule::in(['image', 'youtube', 'pdf'])],
            'professional_id' => ['required', 'integer', Rule::exists('professionals', 'id')],
            'is_featured' => ['required', 'boolean'],
            'image' => [
                Rule::requiredIf($this->input('media_type') === 'image'),
                'nullable',
                'file',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
                'prohibited_unless:media_type,image',
            ],
            'pdf' => [
                Rule::requiredIf($this->input('media_type') === 'pdf'),
                'nullable',
                'file',
                'mimes:pdf',
                'max:20480',
                'prohibited_unless:media_type,pdf',
            ],
            'youtube_url' => [
                Rule::requiredIf($this->input('media_type') === 'youtube'),
                'nullable',
                'url',
                'regex:/^https?:\/\/(www\.)?(youtube\.com\/(watch\?v=|embed\/|shorts\/)|youtu\.be\/)[\w-]+/i',
                'prohibited_unless:media_type,youtube',
            ],
        ];
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
}
