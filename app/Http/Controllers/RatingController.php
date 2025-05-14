<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaginationRequest;
use App\Http\Requests\StoreRatingRequest;
use App\Http\Requests\UpdateRatingRequest;
use App\Service\Contracts\RatingService;
use App\Traits\HttpResponses;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Log\Logger;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Header;
use Knuckles\Scribe\Attributes\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

#[Group("Rating management", "APIs for managing course ratings")]
class RatingController extends Controller implements HasMiddleware
{
    use HttpResponses;

    public function __construct(
        private readonly RatingService $ratingService,
        protected Logger $logger
    )
    {
    }

    #[Endpoint('Get List Ratings By Pagination for a Course', <<<DESC
  This endpoint allows you to get all ratings for a specific course with pagination.
  You can use this endpoint to see all ratings submitted by users for a particular course.
 DESC)]
    #[Authenticated(true)]
    #[Header('Accept', 'application/json')]
    #[Response(
        content:
        [
            "title" => "Berhasil mendapatkan semua rating berdasarkan penomoran halaman untuk kursus",
            "status" => "STATUS_OK",
            "code" => 200,
            "meta" => [
                "current_page" => 1,
                "last_page" => 1,
                "per_page" => 10,
                "total" => 1
            ],
            "data" => [
                [
                    "id" => "01JV6DHQXD90VBERXHMR225S0C",
                    "userId" => "01JV6DEXCJX7R0XRAZ08BC00T5",
                    "courseId" => "01JV6DBAW1JQR7P11TZETWKB63",
                    "rating" => 5,
                    "comment" => "Kursus yang sangat bagus dan informatif",
                    "createdAt" => "2025-05-14T03:31:27.000000Z",
                    "updatedAt" => "2025-05-14T03:32:53.000000Z"
                ]
            ]
        ],
        status: HttpResponse::HTTP_OK,
        description: 'Successfully'
    )]
    #[Response(
        content: [
            'errors' => [
                [
                    'title' => 'Gagal mendapatkan semua rating berdasarkan penomoran halaman untuk kursus',
                    'details' => 'gagal mendapatkan semua rating berdasarkan penomoran halaman untuk kursus, karena user tidak terautentikasi',
                    'status' => 'STATUS_UNAUTHORIZED',
                    'code' => 401,
                    'meta' => null
                ]
            ]
        ],status:  HttpResponse::HTTP_UNAUTHORIZED,
        description: 'Unauthorized'
    )]
    #[Response(
        content: [
            'errors' => [
                [
                    'title' => 'Gagal mendapatkan semua rating berdasarkan penomoran halaman untuk kursus',
                    'details' => 'gagal mendapatkan semua rating berdasarkan penomoran halaman untuk kursus, karena kegagalan server, untuk selengkapnya di meta properti',
                    'code' => 500,
                    'status' => 'STATUS_INTERNAL_SERVER_ERROR'
                ]
            ]
        ],
        status: HttpResponse::HTTP_INTERNAL_SERVER_ERROR,
        description: 'Internal Server Error'
    )]
    public function index(PaginationRequest $request, string $courseId): JsonResponse
    {
        try {
            $this->logger->info('Retrieving ratings list for course', [
                'user_id' => auth()->id(),
                'course_id' => $courseId,
                'pagination' => $request->validated()
            ]);

            $ratings = $this->ratingService->index($request, $courseId);

            $this->logger->info('Ratings list retrieved successfully', [
                'user_id' => auth()->id(),
                'course_id' => $courseId,
                'count' => $ratings->count()
            ]);

            return $this->successResponse([
                'title' => 'Berhasil mendapatkan semua rating berdasarkan penomoran halaman untuk kursus',
                'code' => HttpResponse::HTTP_OK,
                'status' => 'STATUS_OK',
                'data' => $ratings->collection,
                'meta' => [
                    'current_page' => $ratings->currentPage(),
                    'last_page' => $ratings->lastPage(),
                    'per_page' => $ratings->perPage(),
                    'total' => $ratings->total(),
                ]
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Failed to retrieve ratings list for course', [
                'user_id' => auth()->id(),
                'course_id' => $courseId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            if ($e instanceof HttpResponseException) {
                throw $e;
            }

            throw new HttpResponseException($this->errorInternalToResponse($e, 'Gagal mendapatkan semua rating berdasarkan penomoran halaman untuk kursus'));
        }
    }

    #[Endpoint('Create Rating for a Course', <<<DESC
  This endpoint allows you to create a new rating for a specific course.
  Users can rate courses and provide comments about their experience.
 DESC)]
    #[Authenticated(true)]
    #[Header('Accept', 'application/json')]
    #[Response(
        content:
        [
            "title" => "Berhasil membuat rating untuk kursus",
            "status" => "STATUS_CREATED",
            "code" => 201,
            "meta" => null,
            "data" => [
                "id" => "01JV6DHQXD90VBERXHMR225S0C",
                "userId" => "01JV6DEXCJX7R0XRAZ08BC00T5",
                "courseId" => "01JV6DBAW1JQR7P11TZETWKB63",
                "rating" => 5,
                "comment" => "Kursus yang sangat bagus dan informatif",
                "createdAt" => "2025-05-14T03:31:27.000000Z",
                "updatedAt" => "2025-05-14T03:31:27.000000Z"
            ]
        ],
        status: HttpResponse::HTTP_CREATED,
        description: 'Successfully Created'
    )]
    #[Response(
        content: [
            'errors' => [
                [
                    'title' => 'Gagal membuat rating untuk kursus',
                    'details' => 'gagal membuat rating untuk kursus, karena user tidak terautentikasi',
                    'status' => 'STATUS_UNAUTHORIZED',
                    'code' => 401,
                    'meta' => null
                ]
            ]
        ],status:  HttpResponse::HTTP_UNAUTHORIZED,
        description: 'Unauthorized'
    )]
    #[Response(
        content: [
            'errors' => [
                [
                    'title' => 'Gagal membuat rating untuk kursus',
                    'details' => 'gagal membuat rating untuk kursus, karena kursus tidak ditemukan',
                    'code' => 404,
                    'status' => 'STATUS_NOT_FOUND',
                ]
            ]
        ],
        status: HttpResponse::HTTP_NOT_FOUND,
        description: 'Not Found'
    )]
    #[Response(
        content: [
            'errors' => [
                [
                    'title' => 'Gagal membuat rating untuk kursus',
                    'details' => 'gagal membuat rating untuk kursus, karena user tidak diizinkan melakukan aksi ini',
                    'code' => 403,
                    'status' => 'STATUS_FORBIDDEN',
                ]
            ]
        ],
        status: HttpResponse::HTTP_FORBIDDEN,
        description: 'Forbidden'
    )]
    public function store(StoreRatingRequest $request, string $courseId): JsonResponse
    {
        try {
            $this->logger->info('Creating new rating for course', [
                'user_id' => auth()->id(),
                'course_id' => $courseId,
                'data' => $request->validated()
            ]);

            $rating = $this->ratingService->create($request, $courseId);

            $this->logger->info('Rating created successfully', [
                'user_id' => auth()->id(),
                'course_id' => $courseId,
                'rating_id' => $rating->id
            ]);

            return $this->successResponse([
                'title' => 'Berhasil membuat rating untuk kursus',
                'code' => HttpResponse::HTTP_CREATED,
                'status' => 'STATUS_CREATED',
                'data' => $rating
            ], HttpResponse::HTTP_CREATED);
        } catch (\Exception $e) {
            $this->logger->error('Failed to create rating for course', [
                'user_id' => auth()->id(),
                'course_id' => $courseId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            if ($e instanceof HttpResponseException) {
                throw $e;
            }

            throw new HttpResponseException($this->errorInternalToResponse($e, 'Gagal membuat rating untuk kursus'));
        }
    }

    #[Endpoint('Show Rating Details', <<<DESC
  This endpoint allows you to view details of a specific rating.
  You can use this endpoint to see the full details of a rating, including user and course information.
 DESC)]
    #[Authenticated(true)]
    #[Header('Accept', 'application/json')]
    #[Response(
        content:
        [
            "title" => "Berhasil mendapatkan detail rating untuk kursus",
            "status" => "STATUS_OK",
            "code" => 200,
            "meta" => null,
            "data" => [
                "id" => "01JV6DHQXD90VBERXHMR225S0C",
                "userId" => "01JV6DEXCJX7R0XRAZ08BC00T5",
                "courseId" => "01JV6DBAW1JQR7P11TZETWKB63",
                "rating" => 5,
                "comment" => "Kursus yang sangat bagus dan informatif",
                "createdAt" => "2025-05-14T03:31:27.000000Z",
                "updatedAt" => "2025-05-14T03:32:53.000000Z",
                "user" => [
                    "id" => "01JV6DEXCJX7R0XRAZ08BC00T5",
                    "name" => "pelajar",
                    "email" => "pelajar@gmail.com",
                    "role" => "student",
                    "profile" => [
                        "gender" => "male",
                        "about" => "architecto",
                        "photo" => "http://bailey.com/"
                    ],
                    "createdAt" => "2025-05-14T03:29:54.000000Z",
                    "updatedAt" => "2025-05-14T03:29:54.000000Z"
                ],
                "course" => [
                    "id" => "01JV6DBAW1JQR7P11TZETWKB63",
                    "title" => "Laravel 12",
                    "thumbnail" => "http://bailey.com/",
                    "description" => "Eius et animi quos velit et.",
                    "price" => 50000,
                    "currency" => "IDR",
                    "createdAt" => "2025-05-14T03:27:57.000000Z",
                    "updatedAt" => "2025-05-14T03:27:57.000000Z"
                ]
            ]
        ],
        status: HttpResponse::HTTP_OK,
        description: 'Successfully'
    )]
    #[Response(
        content: [
            'errors' => [
                [
                    'title' => 'Gagal mendapatkan detail rating untuk kursus',
                    'details' => 'gagal mendapatkan detail rating untuk kursus, karena user tidak terautentikasi',
                    'code' => 401,
                    'status' => 'STATUS_UNAUTHORIZED',
                ]
            ]
        ],status:  HttpResponse::HTTP_UNAUTHORIZED,
        description: 'Unauthorized'
    )]
    #[Response(
        content: [
            'errors' => [
                [
                    'title' => 'Gagal mendapatkan detail rating untuk kursus',
                    'details' => 'gagal mendapatkan detail rating untuk kursus, karena detail rating tidak ditemukan',
                    'code' => 404,
                    'status' => 'STATUS_NOT_FOUND'
                ]
            ]
        ],
        status: HttpResponse::HTTP_NOT_FOUND,
        description: 'Not Found'
    )]
    public function show(string $courseId,string $ratingId): JsonResponse
    {
        try {
            $this->logger->info('Retrieving rating details', [
                'user_id' => auth()->id(),
                'rating_id' => $ratingId
            ]);

            $rating = $this->ratingService->show($ratingId);

            $this->logger->info('Rating details retrieved successfully', [
                'user_id' => auth()->id(),
                'rating_id' => $ratingId
            ]);

            return $this->successResponse([
                'title' => 'Berhasil mendapatkan detail rating untuk kursus',
                'code' => HttpResponse::HTTP_OK,
                'status' => 'STATUS_OK',
                'data' => $rating
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Failed to retrieve rating details', [
                'user_id' => auth()->id(),
                'rating_id' => $ratingId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            if ($e instanceof HttpResponseException) {
                throw $e;
            }

            throw new HttpResponseException($this->errorInternalToResponse($e, 'Gagal mendapatkan detail rating untuk kursus'));
        }
    }

    #[Endpoint('Update Rating', <<<DESC
  This endpoint allows you to update an existing rating.
  Users can modify their rating score and/or comment for a course they've previously rated.
 DESC)]
    #[Authenticated(true)]
    #[Header('Accept', 'application/json')]
    #[Response(
        content:
        [
            "title" => "Berhasil memperbarui rating untuk kursus",
            "status" => "STATUS_OK",
            "code" => 200,
            "meta" => null,
            "data" => [
                "id" => "01JV6DHQXD90VBERXHMR225S0C",
                "userId" => "01JV6DEXCJX7R0XRAZ08BC00T5",
                "courseId" => "01JV6DBAW1JQR7P11TZETWKB63",
                "rating" => 4,
                "comment" => "Kursus ini bagus tetapi bisa lebih baik lagi",
                "createdAt" => "2025-05-14T03:31:27.000000Z",
                "updatedAt" => "2025-05-14T03:35:53.000000Z"
            ]
        ],
        status: HttpResponse::HTTP_OK,
        description: 'Successfully Updated'
    )]
    #[Response(
        content: [
            'errors' => [
                [
                    'title' => 'Gagal memperbarui rating untuk kursus',
                    'details' => 'gagal memperbarui rating untuk kursus, karena user tidak terautentikasi',
                    'code' => 401,
                    'status' => 'STATUS_UNAUTHORIZED',
                ]
            ]
        ],status:  HttpResponse::HTTP_UNAUTHORIZED,
        description: 'Unauthorized'
    )]
    #[Response(
        content: [
            'errors' => [
                [
                    'title' => 'Gagal memperbarui rating untuk kursus',
                    'details' => 'gagal memperbarui rating untuk kursus, karena kursus tidak ditemukan',
                    'code' => 404,
                    'status' => 'STATUS_NOT_FOUND',
                ]
            ]
        ],
        status: HttpResponse::HTTP_NOT_FOUND,
        description: 'Not Found'
    )]
    #[Response(
        content: [
            'errors' => [
                [
                    'title' => 'Gagal memperbarui rating untuk kursus',
                    'details' => 'gagal memperbarui rating untuk kursus, karena user tidak diizinkan melakukan aksi ini',
                    'code' => 403,
                    'status' => 'STATUS_FORBIDDEN',
                ]
            ]
        ],
        status: HttpResponse::HTTP_FORBIDDEN,
        description: 'Forbidden'
    )]
    public function update(UpdateRatingRequest $request, string $courseId,string $ratingId): JsonResponse
    {
        try {
            $this->logger->info('Updating rating', [
                'user_id' => auth()->id(),
                'rating_id' => $ratingId,
                'data' => $request->validated()
            ]);

            $rating = $this->ratingService->update($request, $ratingId);

            $this->logger->info('Rating updated successfully', [
                'user_id' => auth()->id(),
                'rating_id' => $ratingId
            ]);

            return $this->successResponse([
                'title' => 'Berhasil memperbarui rating untuk kursus',
                'code' => HttpResponse::HTTP_OK,
                'status' => 'STATUS_OK',
                'data' => $rating
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Failed to update rating', [
                'user_id' => auth()->id(),
                'rating_id' => $ratingId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            if ($e instanceof HttpResponseException) {
                throw $e;
            }

            throw new HttpResponseException($this->errorInternalToResponse($e, 'Gagal memperbarui rating untuk kursus'));
        }
    }

    #[Endpoint('Delete Rating', <<<DESC
  This endpoint allows you to delete a rating.
  Users can remove their ratings from courses they've previously rated.
 DESC)]
    #[Authenticated(true)]
    #[Header('Accept', 'application/json')]
    #[Response(
        content:
        [
            "title" => "Berhasil menghapus rating untuk kursus",
            "status" => "STATUS_OK",
            "code" => 200,
            "meta" => [
                "user_id" => "01JV6DEXCJX7R0XRAZ08BC00T5",
                "rating_id" => "01JV6DHQXD90VBERXHMR225S0C"
            ],
            "data" => null
        ],
        status: HttpResponse::HTTP_OK,
        description: 'Successfully Deleted'
    )]
    #[Response(
        content: [
            'errors' => [
                [
                    'title' => 'Gagal menghapus rating untuk kursus',
                    'details' => 'gagal menghapus rating untuk kursus, karena user tidak terautentikasi',
                    'code' => 401,
                    'status' => 'STATUS_UNAUTHORIZED',
                ]
            ]
        ],status:  HttpResponse::HTTP_UNAUTHORIZED,
        description: 'Unauthorized'
    )]
    #[Response(
        content: [
            'errors' => [
                [
                    'title' => 'Gagal menghapus rating untuk kursus',
                    'details' => 'gagal menghapus rating untuk kursus, karena detail rating tidak ditemukan',
                    'code' => 404,
                    'status' => 'STATUS_NOT_FOUND'
                ]
            ]
        ],
        status: HttpResponse::HTTP_NOT_FOUND,
        description: 'Not Found'
    )]
    public function destroy(string $courseId, string $ratingId): JsonResponse
    {
        try {
            $this->logger->info('Deleting rating', [
                'user_id' => auth()->id(),
                'rating_id' => $ratingId
            ]);

            $result = $this->ratingService->destroy($ratingId);

            $this->logger->info('Rating deleted successfully', [
                'user_id' => auth()->id(),
                'rating_id' => $ratingId
            ]);

            return $this->successResponse([
                'title' => 'Berhasil menghapus rating untuk kursus',
                'code' => HttpResponse::HTTP_OK,
                'status' => 'STATUS_OK',
                'data' => null,
                'meta' => $result
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Failed to delete rating', [
                'user_id' => auth()->id(),
                'rating_id' => $ratingId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            if ($e instanceof HttpResponseException) {
                throw $e;
            }

            throw new HttpResponseException($this->errorInternalToResponse($e, 'Gagal menghapus rating untuk kursus'));
        }
    }

    /**
     * Define middleware for this controller.
     *
     * @return array
     */
    public static function middleware(): array
    {
        return [
            new Middleware('auth:api')
        ];
    }
}
