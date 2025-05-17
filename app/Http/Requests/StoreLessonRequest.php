<?php

namespace App\Http\Requests;

use App\Traits\HttpResponses;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class StoreLessonRequest extends FormRequest
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
                'max:255',
            ],
            'description' => [
                'required',
                'string'
            ],
            'video_link' => [
                'required',
                'string',
                'url:http,https'
            ],
            'duration' => [
                'required',
                'integer',
            ],
            'order_number' => [
                'required',
                'integer',
                Rule::unique('lessons', 'order_number')->where(function ($query) {
                    return $query->where('course_id', $this->route('courseId'));
                })
            ]
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Judul wajib diisi.',
            'title.string' => 'Judul harus berupa teks.',
            'title.max' => 'Judul maksimal :max karakter.',

            'description.required' => 'Deskripsi wajib diisi.',
            'description.string' => 'Deskripsi harus berupa teks.',

            'video_link.required' => 'Link video wajib diisi.',
            'video_link.string' => 'Link video harus berupa teks.',
            'video_link.url' => 'Link video harus berupa URL yang valid (http atau https).',

            'duration.required' => 'Durasi wajib diisi.',
            'duration.integer' => 'Durasi harus berupa angka.',

            'order_number.required' => 'Nomor urutan wajib diisi.',
            'order_number.integer' => 'Nomor urutan harus berupa angka.',
            'order_number.unique' => 'Nomor urutan sudah terpakai.',
        ];
    }


    public function validationData(): array
    {
        return $this->only('title', 'description', 'video_link', 'duration', 'order_number');
    }
}
