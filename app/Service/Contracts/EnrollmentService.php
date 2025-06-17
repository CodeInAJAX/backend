<?php

namespace App\Service\Contracts;

use App\Http\Requests\PaginationRequest;
use App\Http\Resources\EnrollmentResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

interface EnrollmentService
{
    public function index(PaginationRequest $data) : AnonymousResourceCollection;

    public function show(string $id) : EnrollmentResource;

    public function delete(string $id) : array;
}
