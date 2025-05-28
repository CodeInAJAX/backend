<?php

namespace App\Http\Requests;

use App\Traits\HttpResponses;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateLessonRequest extends FormRequest
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
                'max:255',
            ],
            'description' => [
                'sometimes',
                'string'
            ],
            'video_link' => [
                'sometimes',
                'string',
                'url:http,https'
            ],
            'duration' => [
                'sometimes',
                'integer',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'title.string' => 'Judul harus berupa teks.',
            'title.max' => 'Judul maksimal :max karakter.',

            'description.string' => 'Deskripsi harus berupa teks.',

            'video_link.string' => 'Link video harus berupa teks.',
            'video_link.url' => 'Link video harus berupa URL yang valid (http atau https).',

            'duration.integer' => 'Durasi harus berupa angka.',

        ];
    }
    public function validationData(): array
    {
        return $this->only('title', 'description', 'video_link', 'duration', 'order_number');
    }
}
