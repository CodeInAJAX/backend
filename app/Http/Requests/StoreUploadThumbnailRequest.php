<?php

namespace App\Http\Requests;

use App\Traits\HttpResponses;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreUploadThumbnailRequest extends FormRequest
{
    use HttpResponses;
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user('api')->hasRole('admin') || $this->user('api')->hasRole('mentor');
    }

    public function failedAuthorization()
    {
        throw new HttpResponseException($this->errorResponse([
            [
                'title' => 'User dilarang untuk melakukan permintaan',
                'details' => 'Hanya admin dan mentor yang dapat melakukan permintaan',
                'code' => 403,
                'status' => 'STATUS_FORBIDDEN'
            ]
        ]));

    }



    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'thumbnail' => [
                'required',
                'file',
                'image',
                'mimes:jpeg,jpg,png',
                'max:10240',
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
            'thumbnail.required' => 'Foto profil wajib diunggah.',
            'thumbnail.file' => 'File yang diunggah harus berupa berkas yang valid.',
            'thumbnail.image' => 'File harus berupa gambar.',
            'thumbnail.mimes' => 'Format gambar harus berupa jpeg, jpg, atau png.',
            'thumbnail.max' => 'Ukuran gambar maksimal 10MB.',
        ];
    }


    public function validationData(): array
    {
        return $this->only('thumbnail');
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            $this->errorValidatorToResponse($validator)
        );
    }
}
