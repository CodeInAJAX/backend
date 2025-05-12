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
                Rule::email()
                    ->rfcCompliant(strict: false)
                    ->validateMxRecord()
                    ->preventSpoofing(),
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
