<?php

namespace App\Http\Requests;

use App\Traits\HttpResponses;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreLessonCompletionRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'lesson_id' => [
                'required',
                'string',
                'exists:lessons,id',
            ],
            'watch_duration' => [
                'required',
                'integer',
                'min:0',
            ],
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            $this->errorValidatorToResponse($validator)
        );
    }

    public function messages(): array
    {
        return [
            'lesson_id.required' => 'ID pelajaran wajib diisi.',
            'lesson_id.string' => 'ID pelajaran harus berupa teks.',
            'lesson_id.exists' => 'ID pelajaran tidak ditemukan dalam data.',

            'watch_duration.required' => 'Durasi tontonan wajib diisi.',
            'watch_duration.integer' => 'Durasi tontonan harus berupa angka.',
            'watch_duration.min' => 'Durasi tontonan tidak boleh kurang dari 0 detik.',
        ];
    }


    public function validationData() :array {
        return [
            'lesson_id' => $this->route('id'),
            'watch_duration' => $this->get('watch_duration'),
        ];
    }
}
