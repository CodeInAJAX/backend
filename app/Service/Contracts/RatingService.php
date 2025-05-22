<?php

namespace App\Service\Contracts;

use App\Http\Requests\PaginationRequest;
use App\Http\Requests\StoreRatingRequest;
use App\Http\Requests\UpdateRatingRequest;
use App\Http\Resources\RatingResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

interface RatingService
{
    public function index(PaginationRequest $data) :AnonymousResourceCollection;

    public function create(StoreRatingRequest $data) :RatingResource;

    public function show(string $id) :RatingResource;

    public function update(UpdateRatingRequest $data, string $id) :RatingResource;

    public function destroy(string $id) :array;
}
