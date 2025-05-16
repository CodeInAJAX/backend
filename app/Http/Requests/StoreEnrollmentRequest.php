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
        return $this->user('api')->hasRole('student');
    }

    public function failedAuthorization()
    {
        throw new HttpResponseException($this->errorResponse([
            [
                'title' => 'User tidak diizinkan untuk melakukan permintaan',
                'details' => 'Hanya user berperan siswa yang dapat melakukan permintaan',
                'code' => 403,
                'status' => 'STATUS_FORBIDDEN',
            ]
        ]));
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
                Rule::unique('enrollments', 'course_id')->where(function ($query) {
                    return $query->where('student_id', $this->user('api')->id);
                })
            ]
        ];
    }

    public function messages(): array {
        return [
            'course_id.required' => 'ID Kursus harus diisi',
            'course_id.string' => 'ID Kursus tidak valid',
            'course_id.exists' => 'ID Kursus tidak ditemukan',
            'course_id.unique' => 'ID Kursus sudah di berada di pendaftaran kursus',
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
