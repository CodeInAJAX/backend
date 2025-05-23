<?php

namespace App\Http\Requests;

use App\Traits\HttpResponses;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreUploadProfilePhotoRequest extends FormRequest
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
            'image' => [
                'required',
                'file',
                'image',
                'mimes:jpeg,jpg,png',
                'max:4096',
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
            'image.required' => 'Foto profil wajib diunggah.',
            'image.file' => 'File yang diunggah harus berupa berkas yang valid.',
            'image.image' => 'File harus berupa gambar.',
            'image.mimes' => 'Format gambar harus berupa jpeg, jpg, atau png.',
            'image.max' => 'Ukuran gambar maksimal 4MB.',
        ];
    }


    public function validationData(): array
    {
        return $this->only('image');
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            $this->errorValidatorToResponse($validator)
        );
    }
}
