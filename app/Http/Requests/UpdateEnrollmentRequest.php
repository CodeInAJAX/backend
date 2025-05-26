<?php

namespace App\Http\Requests;

use App\Enums\Status;
use App\Traits\HttpResponses;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UpdateEnrollmentRequest extends FormRequest
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
            'status' => [
                'required',
                'string',
                Rule::enum(Status::class),
            ]
        ];
    }

    public function messages(): array {
        return [
            'status.string' => 'Status pendaftaraan kursus harus berupa teks.',
            'status.Illuminate\Validation\Rules\Enum' => 'Status pendaftaraan kursus yang dipilih tidak valid.',
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
        return $this->only('course_id','status');
    }
}
