<?php

namespace App\Http\Requests;

use App\Enums\Gender;
use App\Enums\Role;
use App\Traits\HttpResponses;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
use \Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rules\Password;

class StoreUserRequest extends FormRequest
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
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'email' => [
                'required',
                'email',
                'unique:users,email'
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'max:255',
            ],
            'role' => [
                'required',
                'string',
                Rule::enum(Role::class)
            ],
            'photo' => [
                'required',
                'string',
                'url:http,https'
            ],
            'gender' => [
                'required',
                'string',
                Rule::enum(Gender::class)
            ],
            'about' => [
                'string',
            ]
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama wajib diisi.',
            'name.string' => 'Nama harus berupa teks.',
            'name.max' => 'Nama maksimal 255 karakter.',

            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah digunakan.',

            'password.required' => 'Kata sandi wajib diisi.',
            'password.string' => 'Kata sandi harus berupa teks.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
            'password.max' => 'Kata sandi maksimal 255 karakter.',

            'role.required' => 'Peran wajib diisi.',
            'role.string' => 'Peran harus berupa teks.',
            'role.Illuminate\Validation\Rules\Enum' => 'Peran yang dipilih tidak valid.',

            'photo.required' => 'URL foto wajib diisi.',
            'photo.string' => 'URL foto harus berupa teks.',
            'photo.url' => 'URL foto harus berupa URL yang valid (http atau https).',

            'gender.required' => 'Jenis kelamin wajib diisi.',
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
        return $this->only('name','email', 'password', 'role', 'gender', 'about', 'photo');
    }
}
