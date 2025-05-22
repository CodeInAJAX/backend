<?php

namespace App\Http\Requests;

use App\Enums\Status;
use App\Traits\HttpResponses;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class StoreEnrollmentRequest extends FormRequest
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
            'course_id' => [
                'required',
                'string',
                'exists:courses,id',
            ]
        ];
    }

    public function messages(): array {
        return [
            'course_id.required' => 'ID Kursus harus diisi',
            'course_id.string' => 'ID Kursus tidak valid',
            'course_id.exists' => 'ID Kursus tidak ditemukan',

        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            $this->errorValidatorToResponse($validator)
        );
    }

    public function validationData() :array
    {
        return $this->only('course_id');
    }
}
