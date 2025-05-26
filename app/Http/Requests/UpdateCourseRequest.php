<?php

namespace App\Http\Requests;

use App\Traits\HttpResponses;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateCourseRequest extends FormRequest
{

    use HttpResponses;
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            $this->errorValidatorToResponse($validator)
        );
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'title' => [
                'sometimes',
                'string',
                'min:3',
                'max:255',
            ],
            'thumbnail' => [
                'sometimes',
                'string',
                'url:http,https',
            ],
            'description' => [
                'sometimes',
                'string'
            ],
            'price' => [
                'sometimes',
                'integer',
            ],
            'currency' => [
                'sometimes',
                'string',
            ]
        ];
    }

    public function messages(): array
    {
        return [
            'title.string' => 'Judul harus berupa teks.',
            'title.min' => 'Judul minimal terdiri dari :min karakter.',
            'title.max' => 'Judul maksimal terdiri dari :max karakter.',

            'thumbnail.string' => 'Thumbnail harus berupa teks.',
            'thumbnail.url' => 'Thumbnail harus berupa URL yang valid (http atau https).',

            'description.string' => 'Deskripsi harus berupa teks.',

            'price.integer' => 'Harga harus berupa angka.',

            'currency.string' => 'Mata uang harus berupa teks.',
        ];
    }



    public function validationData(): array
    {
        return $this->only('title','thumbnail', 'description', 'price', 'currency');
    }
}
