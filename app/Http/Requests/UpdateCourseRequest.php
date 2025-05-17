<?php

namespace App\Http\Requests;

use App\Traits\HttpResponses;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateCourseRequest extends FormRequest
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
                'sometimes',
                'string',
                'min:3',
                'max:255',
            ],
            'thumbnail' => [
                'sometimes',
                'string',
                'url:http,https',
            ],
            'description' => [
                'sometimes',
                'string'
            ],
            'price' => [
                'sometimes',
                'integer',
            ],
            'currency' => [
                'sometimes',
                'string',
            ]
        ];
    }

    public function validationData(): array
    {
        return $this->only('title','thumbnail', 'description', 'price', 'currency');
    }
}
