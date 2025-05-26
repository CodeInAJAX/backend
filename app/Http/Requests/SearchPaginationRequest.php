<?php

namespace App\Http\Requests;

use App\Traits\HttpResponses;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class SearchPaginationRequest extends FormRequest
{
    use HttpResponses;
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
            ],
            'search' => [
                'required',
                'string',
            ]
        ];
    }

    public function messages(): array {
        return [
            'page.integer' => 'Halaman harus berupa angka!',
            'page.gt' => 'Halaman harus melebihi nilai 0',
            'size.integer' => 'Ukuran harus berupa angka!',
            'size.gt' => 'Ukuran harus melebihi nilai 0',
            'search.required' => 'Kata kunci pencarian harus diisi!',
            'search.string' => 'Kata kunci pencarian harus berupa string!',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            $this->errorValidatorToResponse($validator)
        );
    }

    public function validationData() :array
    {
        return [
            'page' => $this->query->getInt('page') ?? 1,
            'size' => $this->query->getInt('size') ?? 10,
            'search' => $this->query->getString('search') ?? '',
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
            ],
            'search' => [
                'description' => 'Search query',
                'example' => 'name of course',
            ]
        ];
    }
}
