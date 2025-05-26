<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaginationRequest;
use App\Http\Requests\StoreLessonRequest;
use App\Http\Requests\UpdateLessonRequest;
use App\Service\Contracts\LessonService;
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


#[Group("Lesson management", "APIs for managing lessons")]
class LessonController extends Controller implements HasMiddleware
{
    use HttpResponses;
    public function __construct(
        private readonly LessonService $lessonService,
        protected Logger $logger
    )
    {

    }
    public static function middleware() :array
    {
        return [ new Middleware('auth:api') ];
    }

    #[Endpoint('Get List Lessons By Pagination and Course ID', <<<DESC
  This endpoint allows you to get list lessons.
  It's a really useful endpoint, because this endpoint can see all lessons by pagination and course id.
 DESC)]
    #[Authenticated(true)]
    #[Header('Accept', 'application/json')]
    #[Response(
        content: [
            "title" => "Berhasil mendapatkan semua pelajaran berdasarkan penomoran halaman",
            "status" => "STATUS_OK",
            "code" => 200,
            "meta" => [
                "current_page" => 1,
                "last_page" => 1,
                "per_page" => 10,
                "total" => 2
            ],
            "data" => [
                [
                    "id" => "01JV2J9GEHK6QAFMNAT1XZMDXD",
                    "title" => "Laravel Dasar",
                    "description" => "Eius et animi quos velit et.",
                    "video_link" => "http://www.ernser.org/harum-mollitia-modi-deserunt-aut-ab-provident-perspiciatis-quo.html",
                    "duration" => 3600,
                    "order_number" => 1,
                    "created_at" => "2025-05-12T15:37:22.000000Z",
                    "updated_at" => "2025-05-12T15:37:22.000000Z"
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
                    'title' => 'Gagal mendapatkan semua pelajaran berdasarkan penomoran halaman',
                    'details' => 'Sesuatu ada yang salah, tolong coba lagi',
                    'code' => 500,
                    'status' => 'STATUS_INTERNAL_SERVER_ERROR'
                ]
            ]
        ],
        status: HttpResponse::HTTP_INTERNAL_SERVER_ERROR,
        description: 'Internal Server Error'
    )]
    public function index(PaginationRequest $request, string $courseId) : JsonResponse
    {
        try {
            // Log the method call before execution
            $this->logger->info('Attempting to retrieve lessons', [
                'method' => 'index',
                'page' => $request->input('page', 1),
                'course_id' => $courseId
            ]);
            // Get paginated lessons using lesson service
            $lessons = $this->lessonService->getAll($request, $courseId);

            // Log successful retrieval of lessons
            $this->logger->info('Lessons retrieved successfully', [
                'course_id' => $courseId,
                'page' => $request->input('page', 1)
            ]);

            // Return JSON response with lessons
            return $this->successResponse([
                'title' => 'Berhasil mendapatkan semua pelajaran berdasarkan penomoran halaman',
                'code' => HttpResponse::HTTP_OK,
                'status' => 'STATUS_OK',
                'data' => $lessons->collection,
                'meta' => [
                    'current_page' => $lessons->currentPage(),
                    'last_page' => $lessons->lastPage(),
                    'per_page' => $lessons->perPage(),
                    'total' => $lessons->total(),
                ]
            ]);
        } catch (\Exception $e) {
            // Log any errors that occur
            $this->logger->error('Failed to retrieve lessons', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            if ($e instanceof HttpResponseException ) {
                throw $e;
            }

            // Return error response
            throw new HttpResponseException($this->errorInternalToResponse($e,'Gagal mendapatkan semua pelajaran berdasarkan penomoran halaman'));
        }
    }


    #[Endpoint('Create new Lesson By Course ID', <<<DESC
  This endpoint allows you to create new lesson.
  It's a really useful endpoint, because this endpoint can see create new lesson by course id.
 DESC)]
    #[Authenticated(true)]
    #[Header('Accept', 'application/json')]
    #[Response(
        content:  [
            "title" => "Berhasil membuat pelajaran",
            "status" => "STATUS_OK",
            "code" => 200,
            "meta" => null,
            "data" => [
                "id" => "01JV2J9GEHK6QAFMNAT1XZMDXD",
                "title" => "Laravel Dasar",
                "description" => "Eius et animi quos velit et.",
                "video_link" => "http://www.ernser.org/harum-mollitia-modi-deserunt-aut-ab-provident-perspiciatis-quo.html",
                "duration" => 3600,
                "order_number" => 1,
                "created_at" => "2025-05-12T15:37:22.000000Z",
                "updated_at" => "2025-05-12T15:37:22.000000Z"
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
                    'title' => 'Gagal membuat pelajaran',
                    'details' => 'Sesuatu ada yang salah, tolong coba lagi',
                    'code' => 500,
                    'status' => 'STATUS_INTERNAL_SERVER_ERROR'
                ]
            ]
        ],
        status: HttpResponse::HTTP_INTERNAL_SERVER_ERROR,
        description: 'Internal Server Error'
    )]
    public function store(StoreLessonRequest $request, string $courseId) :JsonResponse
    {
        try {
            // Log the method call before execution
            $this->logger->info('Attempting to create lesson', [
                'method' => 'store',
                'course_id' => $courseId,
                'request_data' => $request->validated()
            ]);

            // Create new lesson using lesson service
            $lesson = $this->lessonService->create($request, $courseId);

            // Log successful lesson creation
            $this->logger->info('Lesson created successfully', [
                'course_id' => $courseId,
                'lesson_id' => $lesson->id
            ]);

            // Return JSON response with created lesson
            return $this->successResponse([
                'title' => 'Berhasil menambahkan pelajaran baru',
                'code' => HttpResponse::HTTP_CREATED,
                'status' => 'STATUS_CREATED',
                'data' => $lesson,
            ]);
        } catch (\Exception $e) {
            // Log any errors during lesson creation
            $this->logger->error('Failed to create lesson', [
                'course_id' => $courseId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            if ($e instanceof HttpResponseException ) {
                throw $e;
            }

            // Return error response
            throw new HttpResponseException($this->errorInternalToResponse($e, 'Gagal menambahkan pelajaran baru'));
        }
    }


    #[Endpoint('Show Lesson By Course ID', <<<DESC
  This endpoint allows you to show lesson.
  It's a really useful endpoint, because this endpoint can see show lesson by course id.
 DESC)]
    #[Authenticated(true)]
    #[Header('Accept', 'application/json')]
    #[Response(
        content: [
            "title" => "Berhasil mendapatkan pelajaran",
            "status" => "STATUS_OK",
            "code" => 200,
            "meta" => null,
            "data" => [
                "id" => "01JV2J9GEHK6QAFMNAT1XZMDXD",
                "title" => "Laravel Dasar",
                "description" => "Eius et animi quos velit et.",
                "video_link" => "http://www.ernser.org/harum-mollitia-modi-deserunt-aut-ab-provident-perspiciatis-quo.html",
                "duration" => 3600,
                "order_number" => 1,
                "created_at" => "2025-05-12T15:37:22.000000Z",
                "updated_at" => "2025-05-12T15:37:22.000000Z",
                "course" => [
                    "id" => "01JV2CB3BZN14GFMM6WCAFYY77",
                    "title" => "Laravel 12",
                    "thumbnail" => "http://bailey.com/",
                    "description" => "Eius et animi quos velit et.",
                    "price" => 16,
                    "currency" => "usd",
                    "createdAt" => "2025-05-12T13:53:23.000000Z",
                    "updatedAt" => "2025-05-12T13:53:23.000000Z"
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
        content:  [
            "errors" => [
                [
                    "title" => "Gagal mendapatkan pelajaran",
                    "details" => "gagal mendapatkan pelajaran karena pelajaran tidak ditemukan",
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
                    'title' => 'Gagal mendapatkan pelajaran',
                    'details' => 'Sesuatu ada yang salah, tolong coba lagi',
                    'code' => 500,
                    'status' => 'STATUS_INTERNAL_SERVER_ERROR'
                ]
            ]
        ],
        status: HttpResponse::HTTP_INTERNAL_SERVER_ERROR,
        description: 'Internal Server Error'
    )]
    public function show(string $courseId, string $lessonId) :JsonResponse
    {
        try {
            // Log the method call before execution
            $this->logger->info('Attempting to retrieve specific lesson', [
                'method' => 'show',
                'lesson_id' => $lessonId
            ]);

            // Retrieve lesson details using lesson service
            $lessonResource = $this->lessonService->show($courseId,$lessonId);

            // Log successful lesson retrieval
            $this->logger->info('Lesson retrieved successfully', [
                'lesson_id' => $lessonId
            ]);

            // Return JSON response with lesson details
            return $this->successResponse([
                'title' => 'Berhasil mendapatkan pelajaran',
                'code' => HttpResponse::HTTP_OK,
                'status' => 'STATUS_OK',
                'data' => $lessonResource,
            ]);
        } catch (\Exception $e) {
            // Log any errors during lesson retrieval
            $this->logger->error('Failed to retrieve lesson', [
                'lesson_id' => $lessonId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            if ($e instanceof HttpResponseException ) {
                throw $e;
            }

            // Return error response
            throw new HttpResponseException($this->errorInternalToResponse($e, 'Gagal mendapatkan pelajaran'));
        }
    }

    #[Endpoint('Update Existing Lesson By Course ID', <<<DESC
  This endpoint allows you to update existing lesson.
  It's a really useful endpoint, because this endpoint can see update existing lesson by course id.
 DESC)]
    #[Authenticated(true)]
    #[Header('Accept', 'application/json')]
    #[Response(
        content: [
            "title" => "Berhasil memperbarui pelajaran",
            "status" => "STATUS_OK",
            "code" => 200,
            "meta" => null,
            "data" => [
                "id" => "01JV2J9GEHK6QAFMNAT1XZMDXD",
                "title" => "Laravel Dasar",
                "description" => "Eius et animi quos velit et.",
                "video_link" => "http://www.ernser.org/harum-mollitia-modi-deserunt-aut-ab-provident-perspiciatis-quo.html",
                "duration" => 3600,
                "order_number" => 1,
                "created_at" => "2025-05-12T15:37:22.000000Z",
                "updated_at" => "2025-05-12T15:37:22.000000Z",
                "course" => [
                    "id" => "01JV2CB3BZN14GFMM6WCAFYY77",
                    "title" => "Laravel 12",
                    "thumbnail" => "http://bailey.com/",
                    "description" => "Eius et animi quos velit et.",
                    "price" => 16,
                    "currency" => "usd",
                    "createdAt" => "2025-05-12T13:53:23.000000Z",
                    "updatedAt" => "2025-05-12T13:53:23.000000Z"
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
        content:  [
            "errors" => [
                [
                    "title" => "Gagal memperbarui pelajaran",
                    "details" => "gagal memperbarui pelajaran karena pelajaran tidak ditemukan",
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
                    'title' => 'Gagal memperbarui pelajaran',
                    'details' => 'Sesuatu ada yang salah, tolong coba lagi',
                    'code' => 500,
                    'status' => 'STATUS_INTERNAL_SERVER_ERROR'
                ]
            ]
        ],
        status: HttpResponse::HTTP_INTERNAL_SERVER_ERROR,
        description: 'Internal Server Error'
    )]
    public function update(UpdateLessonRequest $request, string $courseId, string $lessonId) :JsonResponse
    {
        try {
            // Log the method call before execution
            $this->logger->info('Attempting to update lesson', [
                'method' => 'update',
                'lesson_id' => $lessonId,
                'request_data' => $request->validated()
            ]);

            // Update lesson using lesson service
            $updatedLesson = $this->lessonService->update($request, $courseId, $lessonId);

            // Log successful lesson update
            $this->logger->info('Lesson updated successfully', [
                'lesson_id' => $lessonId
            ]);

            // Return JSON response with updated lesson
            return $this->successResponse([
                'title' => 'Berhasil memperbarui pelajaran',
                'code' => HttpResponse::HTTP_OK,
                'status' => 'STATUS_OK',
                'data' => $updatedLesson,
            ]);
        } catch (\Exception $e) {
            // Log any errors during lesson update
            $this->logger->error('Failed to update lesson', [
                'lesson_id' => $lessonId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            if ($e instanceof HttpResponseException ) {
                throw $e;
            }

            // Return error response
            throw new  HttpResponseException($this->errorInternalToResponse($e, 'Gagal memperbarui pelajaran'));
        }
    }

    #[Endpoint('Delete Existing Lesson By Course ID', <<<DESC
  This endpoint allows you to delete existing lesson.
  It's a really useful endpoint, because this endpoint can see delete existing lesson by course id.
 DESC)]
    #[Authenticated(true)]
    #[Header('Accept', 'application/json')]
    #[Response(
        content:  [
            "title" => "Berhasil menghapus pelajaran",
            "status" => "STATUS_OK",
            "code" => 200,
            "meta" => [
                "mentor_id" => "01JV2C47X62ESH0PR53BWF6C0D",
                "deleted_lesson_id" => "01JV2J9GEHK6QAFMNAT1XZMDXD"
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
        content:  [
            "errors" => [
                [
                    "title" => "Gagal menghapus pelajaran",
                    "details" => "gagal menghapus pelajaran karena pelajaran tidak ditemukan",
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
                    'title' => 'Gagal menghapus pelajaran',
                    'details' => 'Sesuatu ada yang salah, tolong coba lagi',
                    'code' => 500,
                    'status' => 'STATUS_INTERNAL_SERVER_ERROR'
                ]
            ]
        ],
        status: HttpResponse::HTTP_INTERNAL_SERVER_ERROR,
        description: 'Internal Server Error'
    )]
    public function destroy(string $courseId,string $lessonId) :JsonResponse
    {
        try {
            // Log the method call before execution
            $this->logger->info('Attempting to delete lesson', [
                'method' => 'destroy',
                'lesson_id' => $lessonId
            ]);

            // Delete lesson using lesson service
            $result = $this->lessonService->delete($courseId, $lessonId);

            // Log successful lesson deletion
            $this->logger->info('Lesson deleted successfully', [
                'lesson_id' => $lessonId
            ]);

            // Return JSON response confirming deletion
            return $this->successResponse(
                [
                    'title' => 'Berhasil menghapus pelajaran',
                    'code' => HttpResponse::HTTP_OK,
                    'status' => 'STATUS_OK',
                    'data' => null,
                    'meta' => $result
                ]
            );
        } catch (\Exception $e) {
            // Log any errors during lesson deletion
            $this->logger->error('Failed to delete lesson', [
                'lesson_id' => $lessonId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            if ($e instanceof HttpResponseException ) {
                throw $e;
            }

            // Return error response
            throw new HttpResponseException($this->errorInternalToResponse($e, 'Gagal menghapus pelajaran'));
        }
    }
}
