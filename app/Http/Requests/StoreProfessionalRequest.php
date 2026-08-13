<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class StoreProfessionalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'role_title' => ['required', 'string', 'max:255'],
            'bio' => ['required', 'string'],
            'skills' => ['required', 'array', 'min:1'],
            'skills.*' => ['required', 'string', 'max:255'],
            'service_id' => ['required', 'integer', Rule::exists('services', 'id')],
            'status' => ['required', 'string', Rule::in(['active', 'inactive'])],
            'is_featured' => ['required', 'boolean'],
            'photo' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
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
    protected function prepareForValidation(): void
{
    if ($this->has('skills') && is_string($this->input('skills'))) {
        $this->merge([
            'skills' => array_values(array_filter(array_map(
                'trim',
                explode(',', $this->input('skills'))
            ))),
        ]);
    }
}
}
