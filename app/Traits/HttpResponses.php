<?php
namespace App\Traits;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\JsonResponse;

trait HttpResponses
{
    public function successResponse(array $payload): JsonResponse
    {
        return response()->json([
            'title'   => $payload['title'] ?? '',
            'status'  => $payload['status'] ?? 'STATUS_OK',
            'code'    => $payload['code'] ?? 200,
            'meta'    => $payload['meta'] ?? null,
            'data'    => $payload['data'] ?? null,
        ], $payload['code'] ?? 200);
    }

    public function errorResponse(array $payloads): JsonResponse
    {
        $errors = [];
        foreach ($payloads as $payload) {
            $errors[] = [
                'title'   => $payload['title'] ?? 'Error',
                'details' => $payload['details'] ?? '',
                'status'  => $payload['status'] ?? 'error',
                'code'    => $payload['code'] ?? 400,
                'meta'    => $payload['meta'] ?? null,
            ];
        }
        return response()->json([
            'errors' => $errors
        ], $payloads[0]['code'] ?? 400);
    }

    public function errorValidatorToResponse(Validator $validator): JsonResponse
    {
        $errors = $validator->errors()->toArray();
        $errorsResponse = [];
        foreach ($errors as $key => $value ) {
            $errorsResponse[] = [
                'title' => 'Users Request Validation Failed',
                'details' => $value,
                'code' => 400,
                'status' => 'Bad Request',
            ];
        }
        return response()->json([
            'errors' => $errorsResponse
        ], 400);
    }
}

