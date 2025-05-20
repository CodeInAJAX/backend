<?php

namespace App\Http\Requests;

use App\Traits\HttpResponses;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class DestroyThumbnailRequest extends FormRequest
{
    use HttpResponses;
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user('api')->hasRole('admin') || $this->user('api')->hasRole('mentor');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'thumbnail_path' => [
                'required',
                'string',
            ]
        ];
    }

    /**
     * Pesan kesalahan kustom untuk validasi.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'thumbnail_path.required' => 'Jalur Foto thumbnail wajib diisi.',
            'thumbnail_path.string' => 'Jalur Foto thumbnail harus berupa teks.',
        ];
    }


    public function validationData(): array
    {
        return $this->only('thumbnail_path');
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            $this->errorValidatorToResponse($validator)
        );
    }
}
