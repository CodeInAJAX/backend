<?php

namespace App\Http\Requests;

use App\Enums\PaymentMethod;
use App\Traits\HttpResponses;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class StorePaymentRequest extends FormRequest
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
        throw new HttpResponseException(
            $this->errorResponse([
                [
                    'title' => 'User tidak diizinkan melakukan permintaan pembuatan pembayaran',
                    'details' => 'Hanya user berperan siswa yang dapat melakukan permintaan pembuatan pembayaran',
                    'code' => 403,
                    'status' => 'STATUS_FORBIDDEN',
                ]
            ])
        );
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
            'course_id' => [
              'required',
              'string',
              'exists:courses,id',
                Rule::unique('payments', 'course_id')->where(function ($query) {
                    return $query->where('user_id', $this->user('api')->id);
                })
            ],
            'amount' => [
                'required',
                'integer',
            ],
            'currency' => [
                'required',
                'string',
            ],
            'payment_method' => [
                'required',
                'string',
                Rule::enum(PaymentMethod::class),
            ]
        ];
    }

    public function messages(): array
    {
        return [
            'course_id.required' => 'ID Kursus harus diisi',
            'course_id.string' => 'ID Kursus tidak valid',
            'course_id.exists' => 'ID Kursus tidak ditemukan',
            'course_id.unique' => 'ID Kursus sudah di bayar',

            'amount.required' => 'Jumlah pembayaran harus diisi.',
            'amount.integer' => 'Jumlah pembayaran harus berupa angka bulat.',

            'currency.required' => 'Mata uang harus diisi.',
            'currency.string' => 'Mata uang harus berupa teks.',

            'payment_method.required' => 'Metode pembayaran harus diisi.',
            'payment_method.string' => 'Metode pembayaran harus berupa teks.',
            'payment_method.Illuminate\Validation\Rules\Enum' => 'Metode pembayaran yang dipilih tidak valid.',
        ];
    }


    public function validationData() :array
    {
        return $this->only('course_id','amount', 'currency', 'payment_method');
    }
}
