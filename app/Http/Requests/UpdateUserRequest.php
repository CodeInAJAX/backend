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
