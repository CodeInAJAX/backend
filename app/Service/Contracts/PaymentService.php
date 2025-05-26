<?php

namespace App\Service\Contracts;

use App\Http\Requests\PaginationRequest;
use App\Http\Requests\StorePaymentRequest;
use App\Http\Requests\UpdatePaymentRequest;
use App\Http\Resources\PaymentResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

interface PaymentService
{
    public function index(PaginationRequest $data) : AnonymousResourceCollection;

    public function show(string $id) : PaymentResource;

    public function create(StorePaymentRequest $data) : PaymentResource;

    public function update(UpdatePaymentRequest $data, string $id) : PaymentResource;

    public function delete(string $id) : array;

}
