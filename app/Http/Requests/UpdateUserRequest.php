<?php

namespace App\Http\Requests;

use App\Enums\Gender;
use App\Traits\HttpResponses;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;

class UpdateUserRequest extends FormRequest
{
    use HttpResponses;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Allow the request if the user is authenticated
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        $user = auth('api')->user();

        return [
            'name' => [
                'sometimes',
                'string',
                'max:255',
            ],
            'email' => [
                'sometimes',
                Rule::email()
                    ->rfcCompliant(strict: false)
                    ->validateMxRecord()
                    ->preventSpoofing(),
                'unique:users,email'
            ],
            'password' => [
                'sometimes',
                'string',
                'min:8',
                'max:255',
            ],
            'photo' => [
                'sometimes',
                'string',
                'url:http,https'
            ],
            'gender' => [
                'sometimes',
                'string',
                Rule::enum(Gender::class)
            ],
            'about' => [
                'sometimes',
                'string',
            ]
        ];
    }

    public function messages(): array
    {
        return [
            'name.string' => 'Nama harus berupa teks.',
            'name.max' => 'Nama maksimal :max karakter.',

            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah digunakan.',

            'password.string' => 'Kata sandi harus berupa teks.',
            'password.min' => 'Kata sandi minimal :min karakter.',
            'password.max' => 'Kata sandi maksimal :max karakter.',

            'role.string' => 'Peran harus berupa teks.',
            'role.Illuminate\Validation\Rules\Enum' => 'Peran yang dipilih tidak valid.',

            'photo.string' => 'URL foto harus berupa teks.',
            'photo.url' => 'URL foto harus berupa URL yang valid (http atau https).',

            'gender.string' => 'Jenis kelamin harus berupa teks.',
            'gender.Illuminate\Validation\Rules\Enum' => 'Jenis kelamin yang dipilih tidak valid.',

            'about.string' => 'Tentang harus berupa teks.',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            $this->errorValidatorToResponse($validator)
        );
    }

    public function validationData(): array
    {
        return $this->only('name', 'email', 'password', 'gender', 'about', 'photo');
    }
}
