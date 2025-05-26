<?php

namespace App\Http\Requests;

use App\Enums\PaymentMethod;
use App\Enums\StatusPayment;
use App\Traits\HttpResponses;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UpdatePaymentRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'amount' => [
                'sometimes',
                'integer',
            ],
            'currency' => [
                'sometimes',
                'string',
            ],
            'payment_method' => [
                'sometimes',
                'string',
                Rule::enum(PaymentMethod::class),
            ],
            'status' => [
                'sometimes',
                'string',
                Rule::enum(StatusPayment::class),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'amount.integer' => 'Jumlah pembayaran harus berupa angka bulat.',

            'currency.string' => 'Mata uang harus berupa teks.',

            'payment_method.string' => 'Metode pembayaran harus berupa teks.',
            'payment_method.Illuminate\Validation\Rules\Enum' => 'Metode pembayaran yang dipilih tidak valid.',

            'status.string' => 'Status pembayaran harus berupa teks.',
            'status.Illuminate\Validation\Rules\Enum' => 'Status pembayaran yang dipilih tidak valid.',
        ];
    }


    public function validationData() :array
    {
        return $this->only('amount', 'currency', 'payment_method', 'status');
    }
}
