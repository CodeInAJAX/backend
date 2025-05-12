<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLessonCompletionRequest;
use App\Http\Requests\UpdateLessonCompletionRequest;
use App\Models\LessonCompletion;
use App\Service\Contracts\LessonCompletionService;
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

#[Group("Lesson Completion Management", "APIs for managing lesson completions")]
class LessonCompletionController extends Controller implements HasMiddleware
{
    use HttpResponses;

    public function __construct(
        private readonly LessonCompletionService $lessonCompletionService,
        protected Logger $logger
    )
    {

    }

    public static function middleware() :array
    {
        return [ new Middleware('auth:api') ];
    }

    #[Endpoint('Store Lesson Completion', <<<DESC
  This endpoint allows you to create a new lesson completion record.
  It's a really useful endpoint, because it tracks user progress through course lessons.
 DESC)]
    #[Authenticated(true)]
    #[Header('Accept', 'application/json')]
    #[Response(
        content: [
            "title" => "Berhasil menambahkan penyelesaian pelajaran",
            "status" => "STATUS_CREATED",
            "code" => 201,
            "meta" => null,
            "data" => [
                "id" => "01JV3K9GEHK6QBFMNAT1XZMDXY",
                "user_id" => "01JV2C47X62ESH0PR53BWF6C0D",
                "lesson_id" => "01JV2J9GEHK6QAFMNAT1XZMDXD",
                "watch_duration" => "3600",
                "completed_at" => "2025-05-15T10:25:22.000000Z",
                "created_at" => "2025-05-15T10:25:22.000000Z",
                "updated_at" => "2025-05-15T10:25:22.000000Z"
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
                    'title' => 'Gagal menambahkan penyelesaian pelajaran',
                    'details' => 'Sesuatu ada yang salah, tolong coba lagi',
                    'code' => 500,
                    'status' => 'STATUS_INTERNAL_SERVER_ERROR'
                ]
            ]
        ],
        status: HttpResponse::HTTP_INTERNAL_SERVER_ERROR,
        description: 'Internal Server Error'
    )]
    public function store(StoreLessonCompletionRequest $request, string $lessonId) :JsonResponse
    {
        try {
            // Log the method call before execution
            $this->logger->info('Attempting to create lesson completion', [
                'method' => 'store',
                'lesson_id' => $lessonId,
                'request_data' => $request->validated()
            ]);

            // Create new lesson completion using service
            $lessonCompletion = $this->lessonCompletionService->create($request, $lessonId);

            // Log successful lesson completion creation
            $this->logger->info('Lesson completion created successfully', [
                'lesson_id' => $lessonId,
                'lesson_completion_id' => $lessonCompletion->id
            ]);

            // Return JSON response with created lesson completion
            return $this->successResponse([
                'title' => 'Berhasil menambahkan penyelesaian pelajaran',
                'code' => HttpResponse::HTTP_CREATED,
                'status' => 'STATUS_CREATED',
                'data' => $lessonCompletion,
            ]);
        } catch (\Exception $e) {
            // Log any errors during lesson completion creation
            $this->logger->error('Failed to create lesson completion', [
                'lesson_id' => $lessonId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            if ($e instanceof HttpResponseException) {
                throw $e;
            }

            // Return error response
            throw new HttpResponseException($this->errorInternalToResponse($e, 'Gagal menambahkan penyelesaian pelajaran'));
        }
    }

    #[Endpoint('Show Lesson Completion', <<<DESC
  This endpoint allows you to view details of a specific lesson completion.
  It's a really useful endpoint, because it provides information about when a user completed a specific lesson.
 DESC)]
    #[Authenticated(true)]
    #[Header('Accept', 'application/json')]
    #[Response(
        content: [
            "title" => "Berhasil mendapatkan penyelesaian pelajaran",
            "status" => "STATUS_OK",
            "code" => 200,
            "meta" => null,
            "data" => [
                "id" => "01JV3K9GEHK6QBFMNAT1XZMDXY",
                "user_id" => "01JV2C47X62ESH0PR53BWF6C0D",
                "lesson_id" => "01JV2J9GEHK6QAFMNAT1XZMDXD",
                "watch_duration" => "3600",
                "completed_at" => "2025-05-15T10:25:22.000000Z",
                "created_at" => "2025-05-15T10:25:22.000000Z",
                "updated_at" => "2025-05-15T10:25:22.000000Z"
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
                    "title" => "Gagal mendapatkan penyelesaian pelajaran",
                    "details" => "gagal mendapatkan penyelesaian pelajaran karena tidak ditemukan",
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
                    'title' => 'Gagal mendapatkan penyelesaian pelajaran',
                    'details' => 'Sesuatu ada yang salah, tolong coba lagi',
                    'code' => 500,
                    'status' => 'STATUS_INTERNAL_SERVER_ERROR'
                ]
            ]
        ],
        status: HttpResponse::HTTP_INTERNAL_SERVER_ERROR,
        description: 'Internal Server Error'
    )]
    public function show(string $lessonCompletionId) :JsonResponse
    {
        try {
            // Log the method call before execution
            $this->logger->info('Attempting to retrieve specific lesson completion', [
                'method' => 'show',
                'lesson_completion_id' => $lessonCompletionId
            ]);

            // Retrieve lesson completion details using service
            $lessonCompletionResource = $this->lessonCompletionService->show($lessonCompletionId);

            // Log successful lesson completion retrieval
            $this->logger->info('Lesson completion retrieved successfully', [
                'lesson_completion_id' => $lessonCompletionId
            ]);

            // Return JSON response with lesson completion details
            return $this->successResponse([
                'title' => 'Berhasil mendapatkan penyelesaian pelajaran',
                'code' => HttpResponse::HTTP_OK,
                'status' => 'STATUS_OK',
                'data' => $lessonCompletionResource,
            ]);
        } catch (\Exception $e) {
            // Log any errors during lesson completion retrieval
            $this->logger->error('Failed to retrieve lesson completion', [
                'lesson_completion_id' => $lessonCompletionId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            if ($e instanceof HttpResponseException) {
                throw $e;
            }

            // Return error response
            throw new HttpResponseException($this->errorInternalToResponse($e, 'Gagal mendapatkan penyelesaian pelajaran'));
        }
    }

    #[Endpoint('Update Lesson Completion', <<<DESC
  This endpoint allows you to update an existing lesson completion record.
  It's a really useful endpoint, because it allows updating completion status or details.
 DESC)]
    #[Authenticated(true)]
    #[Header('Accept', 'application/json')]
    #[Response(
        content: [
            "title" => "Berhasil memperbarui penyelesaian pelajaran",
            "status" => "STATUS_OK",
            "code" => 200,
            "meta" => null,
            "data" => [
                "id" => "01JV3K9GEHK6QBFMNAT1XZMDXY",
                "user_id" => "01JV2C47X62ESH0PR53BWF6C0D",
                "lesson_id" => "01JV2J9GEHK6QAFMNAT1XZMDXD",
                "watch_duration" => "3600",
                "completed_at" => "2025-05-15T11:30:22.000000Z",
                "created_at" => "2025-05-15T10:25:22.000000Z",
                "updated_at" => "2025-05-15T11:30:22.000000Z"
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
                    "title" => "Gagal memperbarui penyelesaian pelajaran",
                    "details" => "gagal memperbarui penyelesaian pelajaran karena tidak ditemukan",
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
                    'title' => 'Gagal memperbarui penyelesaian pelajaran',
                    'details' => 'Sesuatu ada yang salah, tolong coba lagi',
                    'code' => 500,
                    'status' => 'STATUS_INTERNAL_SERVER_ERROR'
                ]
            ]
        ],
        status: HttpResponse::HTTP_INTERNAL_SERVER_ERROR,
        description: 'Internal Server Error'
    )]
    public function update(UpdateLessonCompletionRequest $request, string $lessonCompletionId) :JsonResponse
    {
        try {
            // Log the method call before execution
            $this->logger->info('Attempting to update lesson completion', [
                'method' => 'update',
                'lesson_completion_id' => $lessonCompletionId,
                'request_data' => $request->validated()
            ]);

            // Update lesson completion using service
            $updatedLessonCompletion = $this->lessonCompletionService->update($request, $lessonCompletionId);

            // Log successful lesson completion update
            $this->logger->info('Lesson completion updated successfully', [
                'lesson_completion_id' => $lessonCompletionId
            ]);

            // Return JSON response with updated lesson completion
            return $this->successResponse([
                'title' => 'Berhasil memperbarui penyelesaian pelajaran',
                'code' => HttpResponse::HTTP_OK,
                'status' => 'STATUS_OK',
                'data' => $updatedLessonCompletion,
            ]);
        } catch (\Exception $e) {
            // Log any errors during lesson completion update
            $this->logger->error('Failed to update lesson completion', [
                'lesson_completion_id' => $lessonCompletionId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            if ($e instanceof HttpResponseException) {
                throw $e;
            }

            // Return error response
            throw new HttpResponseException($this->errorInternalToResponse($e, 'Gagal memperbarui penyelesaian pelajaran'));
        }
    }

    #[Endpoint('Delete Lesson Completion', <<<DESC
  This endpoint allows you to delete an existing lesson completion record.
  It's a really useful endpoint, because it allows removing mistaken or invalid completion records.
 DESC)]
    #[Authenticated(true)]
    #[Header('Accept', 'application/json')]
    #[Response(
        content:  [
            "title" => "Berhasil menghapus penyelesaian pelajaran",
            "status" => "STATUS_OK",
            "code" => 200,
            "meta" => [
                "user_id" => "01JV2C47X62ESH0PR53BWF6C0D",
                "lesson_completion_id" => "01JV3K9GEHK6QBFMNAT1XZMDXY"
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
                    "title" => "Gagal menghapus penyelesaian pelajaran",
                    "details" => "gagal menghapus penyelesaian pelajaran karena tidak ditemukan",
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
                    'title' => 'Gagal menghapus penyelesaian pelajaran',
                    'details' => 'Sesuatu ada yang salah, tolong coba lagi',
                    'code' => 500,
                    'status' => 'STATUS_INTERNAL_SERVER_ERROR'
                ]
            ]
        ],
        status: HttpResponse::HTTP_INTERNAL_SERVER_ERROR,
        description: 'Internal Server Error'
    )]
    public function destroy(string $lessonCompletionId) :JsonResponse
    {
        try {
            // Log the method call before execution
            $this->logger->info('Attempting to delete lesson completion', [
                'method' => 'destroy',
                'lesson_completion_id' => $lessonCompletionId
            ]);

            // Delete lesson completion using service
            $result = $this->lessonCompletionService->delete($lessonCompletionId);

            // Log successful lesson completion deletion
            $this->logger->info('Lesson completion deleted successfully', [
                'lesson_completion_id' => $lessonCompletionId
            ]);

            // Return JSON response confirming deletion
            return $this->successResponse(
                [
                    'title' => 'Berhasil menghapus penyelesaian pelajaran',
                    'code' => HttpResponse::HTTP_OK,
                    'status' => 'STATUS_OK',
                    'data' => null,
                    'meta' => $result
                ]
            );
        } catch (\Exception $e) {
            // Log any errors during lesson completion deletion
            $this->logger->error('Failed to delete lesson completion', [
                'lesson_completion_id' => $lessonCompletionId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            if ($e instanceof HttpResponseException) {
                throw $e;
            }

            // Return error response
            throw new HttpResponseException($this->errorInternalToResponse($e, 'Gagal menghapus penyelesaian pelajaran'));
        }
    }
}
