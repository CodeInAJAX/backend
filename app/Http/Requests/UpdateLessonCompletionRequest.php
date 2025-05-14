<?php

namespace App\Http\Requests;

use App\Traits\HttpResponses;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateLessonCompletionRequest extends FormRequest
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
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
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
            'watch_duration.required' => 'Durasi tontonan wajib diisi.',
            'watch_duration.integer' => 'Durasi tontonan harus berupa angka.',
            'watch_duration.min' => 'Durasi tontonan tidak boleh kurang dari 0 detik.',
        ];
    }

    public function validationData() :array {
        return $this->only('watch_duration');
    }
}
