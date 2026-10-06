<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class UpdateServiceRequest extends FormRequest
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
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'slug' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('services', 'slug')->ignore($this->route('id'))],
            'description' => ['sometimes', 'required', 'string'],
            'icon' => ['sometimes', 'nullable', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
            'inclusions' => ['sometimes', 'nullable', 'array'],
            'inclusions.*' => ['required', 'string', 'max:255'],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            new JsonResponse(
                ['message' => 'Validation failed.', 'errors' => $validator->errors()],
                JsonResponse::HTTP_UNPROCESSABLE_ENTITY,
            ),
        );
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('inclusions') && is_string($this->input('inclusions'))) {
            $this->merge([
                'inclusions' => array_values(array_filter(array_map(
                    'trim',
                    explode(',', $this->input('inclusions')),
                ))),
            ]);
        }
    }
}
