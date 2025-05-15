<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaginationRequest;
use App\Service\Contracts\EnrollmentService;
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

#[Group("Enrollment management", "APIs for managing enrollments")]
class EnrollmentController extends Controller implements HasMiddleware
{
    use HttpResponses;
    public function __construct(
        private readonly EnrollmentService $enrollmentService,
        protected Logger $logger
    )
    {

    }

    #[Endpoint('Get List Enrollment By Pagination', <<<DESC
  This endpoint allows you to get list enrollments by pagination.
  It's a really useful endpoint, because this endpoint can see all enrollment by pagination.
 DESC)]
    #[Authenticated(true)]
    #[Header('Accept', 'application/json')]
    #[Response(
        content:
        [
            "title" => "Berhasil mendapatkan semua pendaftaran kursus berdasarkan penomoran halaman",
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
                    "courseId" => "01JV6DHQXD90VBERXHMR225S0C",
                    "studentId" => "01JV6DEXCJX7R0XRAZ08BC00T5",
                    "status" => "active",
                    "createdAt" => "2025-05-14T03:31:27.000000Z",
                    "updatedAt" => "2025-05-14T03:32:53.000000Z",
                    "course" => [
                        "id" => "01JV6DBAW1JQR7P11TZETWKB63",
                        "title" => "Laravel 12",
                        "thumbnail" => "http://bailey.com/",
                        "description" => "Eius et animi quos velit et.",
                        "price" => 50000,
                        "currency" => "IDR",
                        "createdAt" => "2025-05-14T03:27:57.000000Z",
                        "updatedAt" => "2025-05-14T03:27:57.000000Z"
                    ],
                    "student" => [
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
                    ]
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
                    'title' => 'Users tidak terautentikasi',
                    'details' => 'Kamu harus terautentikasi untuk melakukan aksi ini',
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
                    'title' => 'Gagal semua pendaftaran kursus berdasarkan penomoran halaman',
                    'details' => 'Sesuatu ada yang salah, tolong coba lagi',
                    'code' => 500,
                    'status' => 'STATUS_INTERNAL_SERVER_ERROR'
                ]
            ]
        ],
        status: HttpResponse::HTTP_INTERNAL_SERVER_ERROR,
        description: 'Internal Server Error'
    )]
    public function index(PaginationRequest $request): JsonResponse
    {
        try {
            $this->logger->info('Retrieving enrollment list', [
                'user_id' => auth()->id(),
                'pagination' => $request->validated()
            ]);

            $enrollments = $this->enrollmentService->index($request);

            $this->logger->info('Enrollment list retrieved successfully', [
                'user_id' => auth()->id(),
                'count' => $enrollments->count()
            ]);

            return $this->successResponse([
                'title' => 'Berhasil mendapatkan semua pendaftaran kursus berdasarkan penomoran halaman',
                'code' => HttpResponse::HTTP_OK,
                'status' => 'STATUS_OK',
                'data' => $enrollments->collection,
                'meta' => [
                    'current_page' => $enrollments->currentPage(),
                    'last_page' => $enrollments->lastPage(),
                    'per_page' => $enrollments->perPage(),
                    'total' => $enrollments->total(),
                ]
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Failed to retrieve enrollment list', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            if ( $e instanceof HttpResponseException ) {
                throw $e;
            }

            throw new HttpResponseException($this->errorInternalToResponse($e,'Gagal mendapatkan semua pendaftaran kursus berdasarkan penomoran halaman'));
        }
    }

    #[Endpoint('Show Details Enrollment', <<<DESC
  This endpoint allows you to show detail enrollment.
  It's a really useful endpoint, because this endpoint can see show detail enrollment.
 DESC)]
    #[Authenticated(true)]
    #[Header('Accept', 'application/json')]
    #[Response(
        content:
        [
            "title" => "Berhasil mendapatkan detail pendaftaran kursus",
            "status" => "STATUS_OK",
            "code" => 200,
            "meta" => null,
            "data" => [
                "id" => "01JV6DHQXD90VBERXHMR225S0C",
                "courseId" => "01JV6DHQXD90VBERXHMR225S0C",
                "studentId" => "01JV6DEXCJX7R0XRAZ08BC00T5",
                "status" => "active",
                "createdAt" => "2025-05-14T03:31:27.000000Z",
                "updatedAt" => "2025-05-14T03:32:53.000000Z",
                "course" => [
                    "id" => "01JV6DBAW1JQR7P11TZETWKB63",
                    "title" => "Laravel 12",
                    "thumbnail" => "http://bailey.com/",
                    "description" => "Eius et animi quos velit et.",
                    "price" => 50000,
                    "currency" => "IDR",
                    "createdAt" => "2025-05-14T03:27:57.000000Z",
                    "updatedAt" => "2025-05-14T03:27:57.000000Z"
                ],
                "student" => [
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
                    'title' => 'Users tidak terautentikasi',
                    'details' => 'Kamu harus terautentikasi untuk melakukan aksi ini',
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
                    'title' => 'Gagal mendapatkan detail pendaftaran kursus',
                    'details' => 'Sesuatu ada yang salah, tolong coba lagi',
                    'code' => 500,
                    'status' => 'STATUS_INTERNAL_SERVER_ERROR'
                ]
            ]
        ],
        status: HttpResponse::HTTP_INTERNAL_SERVER_ERROR,
        description: 'Internal Server Error'
    )]
    public function show(string $id): JsonResponse
    {
        try {
            $this->logger->info('Retrieving enrollment details', [
                'user_id' => auth()->id(),
                'enrollment_id' => $id
            ]);

            $enrollment = $this->enrollmentService->show($id);

            $this->logger->info('Enrollment details retrieved successfully', [
                'user_id' => auth()->id(),
                'enrollment_id' => $id
            ]);

            return $this->successResponse([
                'title' => 'Berhasil mendapatkan detail pendaftaran kursus',
                'code' => HttpResponse::HTTP_OK,
                'status' => 'STATUS_OK',
                'data' => $enrollment,
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Failed to retrieve enrollment details', [
                'user_id' => auth()->id(),
                'enrollment_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            if ( $e instanceof HttpResponseException ) {
                throw $e;
            }

            throw new HttpResponseException($this->errorInternalToResponse($e,'Gagal mendapatkan detail pendaftaran kursus'));
        }
    }

    #[Endpoint('Delete Enrollment', <<<DESC
  This endpoint allows you to delete enrollment.
  It's a really useful endpoint, because this endpoint can delete enrollment.
 DESC)]
    #[Authenticated(true)]
    #[Header('Accept', 'application/json')]
    #[Response(
        content:
        [
            "title" => "Berhasil menghapus pendaftaran kursus",
            "status" => "STATUS_OK",
            "code" => 200,
            "meta" => [
                "user_id" => "01JV6DEXCJX7R0XRAZ08BC00T5",
                "enrollment_id" => "01JV6DHQXD90VBERXHMR225S0C",
                "payment_id" => "01JV6DHQX986GBVJ1TASQRN772"
            ],
            "data" => null
        ],
        status: HttpResponse::HTTP_OK,
        description: 'Successfully'
    )]
    #[Response(
        content: [
            'errors' => [
                [
                    'title' => 'Users tidak terautentikasi',
                    'details' => 'Kamu harus terautentikasi untuk melakukan aksi ini',
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
                    'title' => 'Gagal menghapus pendaftaran kursus',
                    'details' => 'Sesuatu ada yang salah, tolong coba lagi',
                    'code' => 500,
                    'status' => 'STATUS_INTERNAL_SERVER_ERROR'
                ]
            ]
        ],
        status: HttpResponse::HTTP_INTERNAL_SERVER_ERROR,
        description: 'Internal Server Error'
    )]
    public function destroy(string $id): JsonResponse
    {
        try {
            $this->logger->info('Deleting enrollment', [
                'user_id' => auth()->id(),
                'enrollment_id' => $id
            ]);

            $result = $this->enrollmentService->delete($id);

            $this->logger->info('Enrollment deleted successfully', [
                'user_id' => auth()->id(),
                'enrollment_id' => $id
            ]);

            return $this->successResponse([
                'title' => 'Berhasil menghapus pendaftaran kursus',
                'code' => HttpResponse::HTTP_OK,
                'status' => 'STATUS_OK',
                'data' => null,
                'meta' => $result
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Failed to delete enrollment', [
                'user_id' => auth()->id(),
                'enrollment_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            if ( $e instanceof HttpResponseException ) {
                throw $e;
            }
            throw new HttpResponseException($this->errorInternalToResponse($e, 'Gagal menghapus pendaftaran'));
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
