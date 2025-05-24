<?php

namespace App\Service\Implements;

use App\Http\Requests\PaginationRequest;
use App\Http\Requests\StoreRatingRequest;
use App\Http\Requests\UpdateRatingRequest;
use App\Http\Resources\RatingResource;
use App\Models\Course;
use App\Models\Rating;
use App\Service\Contracts\RatingService;
use App\Traits\HttpResponses;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Log\Logger;
use Tymon\JWTAuth\JWTGuard;

class RatingServiceImpl implements RatingService
{
    use HttpResponses;

    protected Guard|StatefulGuard|JWTGuard $authGuard;
    public function __construct(
        protected Rating $rating,
        protected Course $course,
        protected Logger $logger,
        AuthFactory $authFactory,
        protected Gate $gate,
    )
    {

    }

    public function index(PaginationRequest $data): AnonymousResourceCollection
    {
        // TODO: Implement index() method.
    }

    public function create(StoreRatingRequest $data): RatingResource
    {
        // TODO: Implement create() method.
    }

    public function show(string $id): RatingResource
    {
        // TODO: Implement show() method.
    }

    public function update(UpdateRatingRequest $data, string $id): RatingResource
    {
        // TODO: Implement update() method.
    }

    public function destroy(string $id): array
    {
        // TODO: Implement destroy() method.
    }
}
