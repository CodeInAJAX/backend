<?php

namespace App\Http\Requests;

use App\Traits\HttpResponses;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreCourseRequest extends FormRequest
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
                'required',
                'string',
                'min:3',
                'max:255',
            ],
            'thumbnail' => [
                'required',
                'string',
                'url:http,https',
            ],
            'description' => [
                'required',
                'string'
            ],
            'price' => [
                'required',
                'integer',
            ],
            'currency' => [
                'required',
                'string',
            ]
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Judul wajib diisi.',
            'title.string' => 'Judul harus berupa teks.',
            'title.min' => 'Judul minimal terdiri dari :min karakter.',
            'title.max' => 'Judul maksimal terdiri dari :max karakter.',

            'thumbnail.required' => 'URL thumbnail wajib diisi.',
            'thumbnail.string' => 'Thumbnail harus berupa teks.',
            'thumbnail.url' => 'Thumbnail harus berupa URL yang valid (http atau https).',

            'description.required' => 'Deskripsi wajib diisi.',
            'description.string' => 'Deskripsi harus berupa teks.',

            'price.required' => 'Harga wajib diisi.',
            'price.integer' => 'Harga harus berupa angka.',

            'currency.required' => 'Mata uang wajib diisi.',
            'currency.string' => 'Mata uang harus berupa teks.',
        ];
    }



    public function validationData(): array
    {
        return $this->only('title','thumbnail', 'description', 'price', 'currency');
    }
}
