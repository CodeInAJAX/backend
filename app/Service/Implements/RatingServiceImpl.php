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
use Illuminate\Database\Eloquent\MissingAttributeException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\HttpResponseException;
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
        $this->authGuard = $authFactory->guard('api');
    }

    public function index(PaginationRequest $data, string $courseId): AnonymousResourceCollection
    {
        try {
            $this->logger->info('start get all rating the course');
            // Authentication
            $user = $this->authGuard->user();
            if (!$user) {
                throw new HttpResponseException(
                    $this->errorResponse([
                        [
                            'title' => 'Gagal mendapatkan semua rating berdasarkan penomoran halaman untuk kursus',
                            'details' => 'gagal mendapatkan semua rating berdasarkan penomoran halaman untuk kursus, karena user tidak terautentikasi',
                            'code' => 401,
                            'status' => 'STATUS_UNAUTHORIZED',
                        ]
                    ])
                );
            }

            $this->logger->info('start the process of validating the user request');
            $page = $data->validated('page', 1);
            $size = $data->validated('size', 10);

            $this->logger->info('start get all rating the course successfully pass the rating process for the course, then find for the rated course');
            $rating = $this->rating->newQuery()->where('course_id', $courseId)->paginate(perPage: $size, page: $page);


            $this->logger->info('successful get all rating process, then return rating result');
            return RatingResource::collection($rating);
        } catch (\Exception $exception) {
            $this->logger->error('failed to get all rating: ' . $exception->getMessage(), [
                'exception_class' => get_class($exception),
                'trace' => $exception->getTraceAsString(),
            ]);
            throw new HttpResponseException($this->errorResponse([
                [
                    'title' => 'Gagal mendapatkan semua rating berdasarkan penomoran halaman untuk kursus',
                    'details' => 'gagal mendapatkan semua rating berdasarkan penomoran halaman untuk kursus, karena kegagalan server, untuk selengkapnya di meta properti',
                    'code' => 500,
                    'status' => 'INTERNAL_SERVER_ERROR',
                ]
            ]));
        }
    }

    public function create(StoreRatingRequest $data, string $courseId): RatingResource
    {
        try {
            $this->logger->info('start create rating the course');
            // Authentication
            $user = $this->authGuard->user();
            if (!$user) {
                throw new HttpResponseException(
                    $this->errorResponse([
                        [
                            'title' => 'Gagal membuat rating untuk kursus',
                            'details' => 'gagal membuat rating untuk kursus, karena user tidak terautentikasi',
                            'code' => 401,
                            'status' => 'STATUS_UNAUTHORIZED',
                        ]
                    ])
                );
            }
            $this->logger->info('start rating the course successfully pass the rating process for the course, then find for the rated course');
            $course = $this->course->newQuery()->find($courseId);
            if (!$course) {
                $this->logger->info('failed to find a course to rate, because it was not found.');
                throw new HttpResponseException($this -> errorResponse([
                    [
                        'title' => 'Gagal membuat rating untuk kursus',
                        'details' => 'gagal membuat rating untuk kursus, karena kursus tidak ditemukan',
                        'code' => 404,
                        'status' => 'STATUS_NOT_FOUND',
                    ]
                ]));
            }
            $this->logger->info('start the process of validating the user request');
            $validated = $data->validated();

            $this->logger->info('start the authorization process to determine if the user is allowed to create a rating for the course.');
            // Authorization
            if (!$this->gate->allows('createRating', $course)) {
                $this->logger->info('failed the authorization process because the user is not allowed');
                throw new HttpResponseException($this->errorResponse([
                    [
                        'title' => 'Gagal membuat rating untuk kursus',
                        'details' => 'gagal membuat rating untuk kursus, karena user tidak diizinkan melakukan aksi ini',
                        'code' => 403,
                        'status' => 'STATUS_FORBIDDEN',
                    ]
                ]));
            };
            $this->logger->info('successful authorization process, then the rating process for the course.');

            $rating = $this->rating->newQuery()->create([
                'user_id' => $user->id,
                'course_id' => $courseId,
                'rating' => $validated['rating'],
                'comment' => $validated['comment'],
            ]);

            return new RatingResource($rating);
        } catch (MissingAttributeException $exception) {
            $this->logger->error('failed to create the rating: missing attribute: ' . $exception->getMessage());
            throw new HttpResponseException($this->errorResponse([
                [
                    'title' => 'Gagal membuat rating untuk kursus',
                    'details' => 'atribut yang diperlukan hilang untuk detail nya di meta properti',
                    'code' => 400,
                    'status' => 'STATUS_BAD_REQUEST',
                    'meta' => [
                        'en' => [
                            'error' => $exception->getMessage(),
                        ]
                    ]
                ]
            ]));
        }
        }

    public function show(string $id): RatingResource
    {
        try {
            $this->logger->info('start show rating the course');
            // Authentication
            $user = $this->authGuard->user();
            if (!$user) {
                throw new HttpResponseException(
                    $this->errorResponse([
                        [
                            'title' => 'Gagal mendapatkan detail rating untuk kursus',
                            'details' => 'gagal mendapatkan detail rating untuk kursus, karena user tidak terautentikasi',
                            'code' => 401,
                            'status' => 'STATUS_UNAUTHORIZED',
                        ]
                    ])
                );
            }
            $this->logger->info('start show rating the course successfully pass the rating process for the course, then find for the rated course');
            $rating = $this->rating->newQuery()->with(['user','course'])->find($id);
            if (!$rating) {
                $this->logger->info('failed to find a rate a course, because it was not found.');
                throw new HttpResponseException($this -> errorResponse([
                    [
                        'title' => 'Gagal mendapatkan detail rating untuk kursus',
                        'details' => 'gagal mendapatkan detail rating untuk kursus, karena kursus tidak ditemukan',
                        'code' => 404,
                        'status' => 'STATUS_NOT_FOUND',
                    ]
                ]));
            }


            $this->logger->info('successful find rating process, then return rating result');
            return new RatingResource($rating);
        } catch (ModelNotFoundException $exception) {
            $this->logger->error('failed to show detail the rating: rating not found : ' . $exception->getMessage());
            throw new HttpResponseException($this->errorResponse([
                [
                    'title' => 'Gagal mendapatkan detail rating untuk kursus',
                    'details' => 'gagal mendapatkan detail rating untuk kursus, karena detail rating tidak ditemukan',
                    'code' => 404,
                    'status' => 'STATUS_NOT_FOUND'
                ]
            ]));
        }
    }

    public function update(UpdateRatingRequest $data, string $id): RatingResource
    {
        try {
            $this->logger->info('start update rating the course');
            // Authentication
            $user = $this->authGuard->user();
            if (!$user) {
                throw new HttpResponseException(
                    $this->errorResponse([
                        [
                            'title' => 'Gagal memperbarui rating untuk kursus',
                            'details' => 'gagal memperbarui rating untuk kursus, karena user tidak terautentikasi',
                            'code' => 401,
                            'status' => 'STATUS_UNAUTHORIZED',
                        ]
                    ])
                );
            }
            $this->logger->info('start update rating the course successfully pass the rating process for the course, then find for the rated course');
            $rating = $this->rating->newQuery()->find($id);
            if (!$rating) {
                $this->logger->info('failed to find a rate a course, because it was not found.');
                throw new HttpResponseException($this -> errorResponse([
                    [
                        'title' => 'Gagal memperbarui rating untuk kursus',
                        'details' => 'gagal memperbarui rating untuk kursus, karena kursus tidak ditemukan',
                        'code' => 404,
                        'status' => 'STATUS_NOT_FOUND',
                    ]
                ]));
            }
            $this->logger->info('start the process of validating the user request');
            $validated = $data->validated();

            $this->logger->info('start the authorization process to determine if the user is allowed to updating a rating for the course.');
            // Authorization
            if (!$this->gate->allows('update', $rating)) {
                $this->logger->info('failed the authorization process because the user is not allowed');
                throw new HttpResponseException($this->errorResponse([
                    [
                        'title' => 'Gagal memperbarui rating untuk kursus',
                        'details' => 'gagal memperbarui rating untuk kursus, karena user tidak diizinkan melakukan aksi ini',
                        'code' => 403,
                        'status' => 'STATUS_FORBIDDEN',
                    ]
                ]));
            };
            $this->logger->info('successful authorization process, then update rating process for the course.');

            if (isset($validated['comment'])) {
                $rating->comment = $validated['comment'];
            }

            if (isset($validated['rating'])) {
                $rating->rating = $validated['rating'];
            }

            $rating->save();

            return new RatingResource($rating);
        } catch (MissingAttributeException $exception) {
            $this->logger->error('failed to update the rating: missing attribute: ' . $exception->getMessage());
            throw new HttpResponseException($this->errorResponse([
                [
                    'title' => 'Gagal memperbarui rating untuk kursus',
                    'details' => 'atribut yang diperlukan hilang untuk detail nya di meta properti',
                    'code' => 400,
                    'status' => 'STATUS_BAD_REQUEST',
                    'meta' => [
                        'en' => [
                            'error' => $exception->getMessage(),
                        ]
                    ]
                ]
            ]));
        }
    }

    public function destroy(string $id): array
    {
        try {
            $this->logger->info('start delete rating the course');
            // Authentication
            $user = $this->authGuard->user();
            if (!$user) {
                throw new HttpResponseException(
                    $this->errorResponse([
                        [
                            'title' => 'Gagal menghapus rating untuk kursus',
                            'details' => 'gagal menghapus rating untuk kursus, karena user tidak terautentikasi',
                            'code' => 401,
                            'status' => 'STATUS_UNAUTHORIZED',
                        ]
                    ])
                );
            }
            $this->logger->info('start delete rating the course successfully pass the rating process for the course, then find for the rated course');
            $rating = $this->rating->newQuery()->find($id);
            if (!$rating) {
                $this->logger->info('failed to find a rate a course, because it was not found.');
                throw new HttpResponseException($this -> errorResponse([
                    [
                        'title' => 'Gagal menghapus rating untuk kursus',
                        'details' => 'gagal menghapus rating untuk kursus, karena kursus tidak ditemukan',
                        'code' => 404,
                        'status' => 'STATUS_NOT_FOUND',
                    ]
                ]));
            }

            $this->logger->info('successful find rating process, then the rating deleted');
            $rating->delete();
            return [
                'user_id' => $user->id,
                'rating_id' => $id,
            ];
        } catch (ModelNotFoundException $exception) {
            $this->logger->error('failed to delete the rating: rating not found : ' . $exception->getMessage());
            throw new HttpResponseException($this->errorResponse([
                [
                    'title' => 'Gagal menghapus rating untuk kursus',
                    'details' => 'gagal menghapus rating untuk kursus, karena detail rating tidak ditemukan',
                    'code' => 404,
                    'status' => 'STATUS_NOT_FOUND'
                ]
            ]));
        }
    }

}
