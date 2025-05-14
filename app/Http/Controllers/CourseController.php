<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaginationRequest;
use App\Traits\HttpResponses;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Routing\Controllers\Middleware;
use App\Http\Requests\StoreCourseRequest;
use App\Http\Requests\UpdateCourseRequest;
use App\Service\Contracts\CourseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Log\Logger;
use Illuminate\Routing\Controllers\HasMiddleware;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Header;
use Knuckles\Scribe\Attributes\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

#[Group("Course management", "APIs for managing courses")]
class CourseController extends Controller implements HasMiddleware
{
    use HttpResponses;
    public function __construct(
        private  readonly  CourseService $courseService,
        protected Logger $logger
    )
    {

    }

    #[Endpoint('Get List Courses By Pagination', <<<DESC
  This endpoint allows you to get list courses by pagination.
  It's a really useful endpoint, because this endpoint can see all courses by pagination.
 DESC)]
    #[Authenticated(true)]
    #[Header('Accept', 'application/json')]
    #[Response(
        content: [
            'title' => 'Berhasil mendapatkan semua kursus',
            'code' => 200,
            'status' => 'STATUS_OK',
            'data' => [
                [
                    "id" => "01JV2C8J558TDHGGGBAFG70EEY",
                    "title" => "React 12",
                    "thumbnail" => "http://bailey.com/",
                    "description" => "Eius et animi quos velit et.",
                    "price" => 16,
                    "currency" => "usd",
                    "createdAt" => "2025-05-12T13:52:00.000000Z",
                    "updatedAt" => "2025-05-12T13:52:00.000000Z"
                ],
                [
                    "id" => "01JV2CB3BZN14GFMM6WCAFYY77",
                    "title" => "Laravel 12",
                    "thumbnail" => "http://bailey.com/",
                    "description" => "Eius et animi quos velit et.",
                    "price" => 16,
                    "currency" => "usd",
                    "createdAt" => "2025-05-12T13:53:23.000000Z",
                    "updatedAt" => "2025-05-12T13:53:23.000000Z"
                ]
            ],
            'meta' => [
                'current_page' => 1,
                'last_page' => 1,
                'per_page' => 10,
                'total' => 2,
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
                    'title' => 'Gagal mendapatkan semua kursus',
                    'details' => 'Sesuatu ada yang salah, tolong coba lagi',
                    'code' => 500,
                    'status' => 'STATUS_INTERNAL_SERVER_ERROR'
                ]
            ]
        ],
        status: HttpResponse::HTTP_INTERNAL_SERVER_ERROR,
        description: 'Internal Server Error'
    )]
    public function index(PaginationRequest $request) : JsonResponse
    {
        try {
            $this->logger->info('processing request for get all courses');
            $courses = $this->courseService->getAll($request);
            $this->logger->info('successfully retrieved all courses');

            return $this->successResponse(
                [
                    'title' => 'Berhasil mendapatkan semua kursus',
                    'code' => 200,
                    'status' => 'STATUS_OK',
                    'data' => $courses->collection,
                    'meta' => [
                        'current_page' => $courses->currentPage(),
                        'last_page' => $courses->lastPage(),
                        'per_page' => $courses->perPage(),
                        'total' => $courses->total(),
                    ]
                ]
            );
        } catch (\Exception $exception) {
            if ($exception instanceof HttpResponseException) {
                throw $exception;
            }
            $this->logger->error('failed processing request for get all courses', [
                'error' => $exception->getMessage()
            ]);
            throw new HttpResponseException($this->errorInternalToResponse($exception, 'Gagal mendapatkan semua kursus'));
        }
    }

    #[Endpoint('Create new Course', <<<DESC
  This endpoint allows you to create new course.
  It's a really useful endpoint, because this endpoint can see create new course.
 DESC)]
    #[Authenticated(true)]
    #[Header('Accept', 'application/json')]
    #[Response(
        content: [
            [
                "title" => "Berhasil membuat kursus",
                "status" => 200,
                "code" => 200,
                "meta" => null,
                "data" => [
                    "id" => "01JV2C8J558TDHGGGBAFG70EEY",
                    "title" => "React 12",
                    "thumbnail" => "http://bailey.com/",
                    "description" => "Eius et animi quos velit et.",
                    "price" => 16,
                    "currency" => "usd",
                    "createdAt" => "2025-05-12T13:52:00.000000Z",
                    "updatedAt" => "2025-05-12T13:52:00.000000Z"
                ]
            ]
        ],
        status: HttpResponse::HTTP_CREATED,
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
                    'title' => 'Gagal membuat kursus',
                    'details' => 'Sesuatu ada yang salah, tolong coba lagi',
                    'code' => 500,
                    'status' => 'STATUS_INTERNAL_SERVER_ERROR'
                ]
            ]
        ],
        status: HttpResponse::HTTP_INTERNAL_SERVER_ERROR,
        description: 'Internal Server Error'
    )]
    public function store(StoreCourseRequest $request) : JsonResponse
    {
        try {
            $this->logger->info('processing request for create course');
            $course = $this->courseService->create($request);
            $this->logger->info('successfully created course');

            return $this->successResponse(
                [
                    'title' => 'Berhasil membuat kursus',
                    'code' => 200,
                    'status' => 'STATUS_OK',
                    'data' => $course
                ]
            );
        } catch (\Exception $exception) {
            if ( $exception instanceof HttpResponseException ) {
                throw $exception;
            }
            $this->logger->error('failed processing request for create course',  [
                'error' => $exception->getMessage()
            ]);
            throw new HttpResponseException($this->errorInternalToResponse($exception, 'Gagal membuat kursus'));
        }
    }

    #[Endpoint('Show Course', <<<DESC
  This endpoint allows you to show course.
  It's a really useful endpoint, because this endpoint can see show course by id.
 DESC)]
    #[Authenticated(true)]
    #[Header('Accept', 'application/json')]
    #[Response(
        content: [
            [
                "title" => "Berhasil mendapatkan kursus",
                "status" => 200,
                "code" => 200,
                "meta" => null,
                "data" => [
                    "id" => "01JV2C8J558TDHGGGBAFG70EEY",
                    "title" => "React 12",
                    "thumbnail" => "http://bailey.com/",
                    "description" => "Eius et animi quos velit et.",
                    "price" => 16,
                    "currency" => "usd",
                    "createdAt" => "2025-05-12T13:52:00.000000Z",
                    "updatedAt" => "2025-05-12T13:52:00.000000Z"
                ]
            ]
        ],
        status: HttpResponse::HTTP_OK,
        description: 'Successfully'
    )]
    #[Response(
        content:    [
            "errors" => [
                [
                    "title" => "Gagal mendapatkan kursus",
                    "details" => "gagal mendapatkan kursus karena tidak ditemukan",
                    "status" => "STATUS_NOT_FOUND",
                    "code" => 404,
                    "meta" => null
                ]
            ]
        ],
        status:  HttpResponse::HTTP_NOT_FOUND,
        description: 'Not Found'
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
                    'title' => 'Gagal mendapatkan kursus',
                    'details' => 'Sesuatu ada yang salah, tolong coba lagi',
                    'code' => 500,
                    'status' => 'STATUS_INTERNAL_SERVER_ERROR'
                ]
            ]
        ],
        status: HttpResponse::HTTP_INTERNAL_SERVER_ERROR,
        description: 'Internal Server Error'
    )]
    public function show(string $id) : JsonResponse
    {
        try {
            $this->logger->info('processing request for show course', [
                'course_id' => $id
            ]);
            $course = $this->courseService->show($id);
            $this->logger->info('successfully retrieved course', [
                'course_id' => $id
            ]);

            return $this->successResponse(
                [
                    'title' => 'Berhasil mendapatkan kursus',
                    'code' => 200,
                    'status' => 'STATUS_OK',
                    'data' => $course
                ]
            );
        } catch (\Exception $exception) {
            if ($exception instanceof HttpResponseException) {
                throw $exception;
            }
            $this->logger->error('failed processing request for show course', [
                'course_id' => $id,
                'error' => $exception->getMessage()
            ]);
            throw new HttpResponseException($this->errorInternalToResponse($exception, 'Gagal mendapatkan kursus'));
        }
    }

    #[Endpoint('Update Course By ID', <<<DESC
  This endpoint allows you to Update Course by ID.
  It's a really useful endpoint, because this endpoint can see update course by id.
 DESC)]
    #[Authenticated(true)]
    #[Header('Accept', 'application/json')]
    #[Response(
        content: [
            [
                "title" => "Berhasil memperbarui kursus",
                "status" => 200,
                "code" => 200,
                "meta" => null,
                "data" => [
                    "id" => "01JV2C8J558TDHGGGBAFG70EEY",
                    "title" => "React 12",
                    "thumbnail" => "http://bailey.com/",
                    "description" => "Eius et animi quos velit et.",
                    "price" => 16,
                    "currency" => "usd",
                    "createdAt" => "2025-05-12T13:52:00.000000Z",
                    "updatedAt" => "2025-05-12T13:52:00.000000Z"
                ]
            ]
        ],
        status: HttpResponse::HTTP_OK,
        description: 'Successfully'
    )]
    #[Response(
        content:    [
            "errors" => [
                [
                    "title" => "Gagal memperbarui kursus",
                    "details" => "gagal memperbarui kursus karena tidak ditemukan",
                    "status" => "STATUS_NOT_FOUND",
                    "code" => 404,
                    "meta" => null
                ]
            ]
        ],
        status:  HttpResponse::HTTP_NOT_FOUND,
        description: 'Not Found'
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
                    'title' => 'Gagal memperbarui kursus',
                    'details' => 'Sesuatu ada yang salah, tolong coba lagi',
                    'code' => 500,
                    'status' => 'STATUS_INTERNAL_SERVER_ERROR'
                ]
            ]
        ],
        status: HttpResponse::HTTP_INTERNAL_SERVER_ERROR,
        description: 'Internal Server Error'
    )]
    public function update(UpdateCourseRequest $request, string $id) :JsonResponse
    {
        try {
            $this->logger->info('processing request for update course', [
                'course_id' => $id
            ]);
            $course = $this->courseService->update($id, $request);
            $this->logger->info('successfully updated course', [
                'course_id' => $id
            ]);

            return $this->successResponse(
                [
                    'title' => 'Berhasil memperbarui kursus',
                    'code' => 200,
                    'status' => 'STATUS_OK',
                    'data' => $course
                ]
            );
        } catch (\Exception $exception) {
            if ($exception instanceof HttpResponseException) {
                throw $exception;
            }
            $this->logger->error('failed processing request for update course', [
                'course_id' => $id,
                'error' => $exception->getMessage()
            ]);
            throw new HttpResponseException($this->errorInternalToResponse($exception, 'Gagal memperbarui kursus'));
        }
    }

    #[Endpoint('Delete Course By ID', <<<DESC
  This endpoint allows you to Delete Course by ID.
  It's a really useful endpoint, because this endpoint can see delete course by id.
 DESC)]
    #[Authenticated(true)]
    #[Header('Accept', 'application/json')]
    #[Response(
        content: [
            [
                "title" => "Berhasil menghapus kursus",
                "status" => "STATUS_OK",
                "code" => 200,
                "meta" => [
                    "deleted_course_id" => "01JV2C8J558TDHGGGBAFG70EEY",
                    "mentor_id" => "01JV2C47X62ESH0PR53BWF6C0D"
                ],
                "data" => null
            ]
        ],
        status: HttpResponse::HTTP_OK,
        description: 'Successfully'
    )]
    #[Response(
        content:    [
            "errors" => [
                [
                    "title" => "Gagal menghapus kursus",
                    "details" => "gagal menghapus kursus karena tidak ditemukan",
                    "status" => "STATUS_NOT_FOUND",
                    "code" => 404,
                    "meta" => null
                ]
            ]
        ],
        status:  HttpResponse::HTTP_NOT_FOUND,
        description: 'Not Found'
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
                    'title' => 'Gagal menghapus kursus',
                    'details' => 'Sesuatu ada yang salah, tolong coba lagi',
                    'code' => 500,
                    'status' => 'STATUS_INTERNAL_SERVER_ERROR'
                ]
            ]
        ],
        status: HttpResponse::HTTP_INTERNAL_SERVER_ERROR,
        description: 'Internal Server Error'
    )]
    public function destroy(string $id) : JsonResponse
    {
        try {
            $this->logger->info('processing request for delete course', [
                'course_id' => $id
            ]);
            $result = $this->courseService->delete($id);
            $this->logger->info('successfully deleted course', [
                'course_id' => $id
            ]);

            return $this->successResponse(
                [
                    'title' => 'Berhasil menghapus kursus',
                    'code' => 200,
                    'status' => 'STATUS_OK',
                    'data' => null,
                    'meta' => $result
                ]
            );
        } catch (\Exception $exception) {
            if ($exception instanceof HttpResponseException) {
                throw $exception;
            }
            $this->logger->error('failed processing request for delete course', [
                'course_id' => $id,
                'error' => $exception->getMessage()
            ]);
            throw new HttpResponseException($this->errorInternalToResponse($exception, 'Gagal menghapus kursus'));
        }
    }

    public static function middleware() : array
    {
        return [ new Middleware('auth:api') ];
    }
}
