<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaginationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'page' => [
                'integer',
                'gt:0',
            ],
            'size' => [
                'integer',
                'gt:0',
            ]
        ];
    }

    public function validationData() :array
    {
        return [
            'page' => $this->query->getInt('page') ?? 1,
            'size' => $this->query->getInt('size') ?? 10,
        ];
    }

    public function bodyParameters() : array
    {
        return [];
    }

    public function queryParameters() : array
    {
        return [
            'page' => [
                'description' => 'Number of pages to show',
                'example' => 1,
            ],
            'size' => [
                'description' => 'Number of sizes to show',
                'example' => 10,
            ]
        ];
    }

}
