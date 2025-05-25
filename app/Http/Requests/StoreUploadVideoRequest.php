<?php

namespace App\Http\Requests;

use App\Traits\HttpResponses;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreUploadVideoRequest extends FormRequest
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
            'video' => [
                'required',
                'file',
                'mimetypes:video/mp4,video/mpeg,video/quicktime,video/x-msvideo',
                'max:102400'
            ]
        ];
    }

    public function messages(): array
    {
        return [
            'video.required' => 'Video wajib diunggah.',
            'video.file' => 'File yang diunggah harus berupa berkas yang valid.',
            'video.mimetypes' => 'Format video tidak didukung. Format yang diperbolehkan: mp4, mpeg, quicktime, avi.',
            'video.max' => 'Ukuran video maksimal 100MB.',
        ];
    }


    public function validationData(): array
    {
        return $this->only('video');
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            $this->errorValidatorToResponse($validator)
        );
    }
}
