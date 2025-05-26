<?php

namespace App\Service\Implements;

use App\Http\Requests\StoreLessonRequest;
use App\Http\Requests\UpdateLessonRequest;
use App\Http\Resources\LessonResource;
use App\Models\Course;
use App\Models\Lesson;
use \Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use App\Service\Contracts\LessonService;
use \App\Http\Requests\PaginationRequest;
use App\Traits\HttpResponses;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Database\Eloquent\MissingAttributeException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Log\Logger;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Tymon\JWTAuth\JWTGuard;

class LessonServiceImpl implements LessonService
{
    use HttpResponses;
    protected StatefulGuard|Guard|JWTGuard $authGuard;
    /**
     * Create a new class instance.
     */
    public function __construct(
        protected Lesson $lesson,
        protected Course $course,
        protected Logger $logger,
        AuthFactory $authFactory,
        protected Gate $gate,
    )
    {
        $this->authGuard = $authFactory->guard('api');
    }

// Modified getAll method with improved error handling for the service implementation
    public function getAll(PaginationRequest $data, string $courseId): AnonymousResourceCollection
    {
        try {
            // Authentication
            $this->logger->info('Before starting the course get all process, the authentication and authorization process first');
            $user = $this->authGuard->user();

            if (!$user) {
                $this->logger->error('Failed authentication process - user not authenticated');
                throw new HttpResponseException(
                    $this->errorResponse([
                        [
                            'title' => 'Gagal mendapatkan semua pelajaran',
                            'details' => 'Gagal mendapatkan semua pelajaran karena user tidak terautentikasi',
                            'code' => HttpResponse::HTTP_UNAUTHORIZED,
                            'status' => 'STATUS_UNAUTHORIZED',
                        ]
                    ])
                );
            }

            // Check if course exists
            $course = $this->course->newQuery()->find($courseId);
            if (!$course) {
                $this->logger->error('Failed to get all lessons: course not found', [
                    'course_id' => $courseId
                ]);

                throw new HttpResponseException(
                    $this->errorResponse([
                        [
                            'title' => 'Gagal mendapatkan semua pelajaran',
                            'details' => 'Gagal mendapatkan semua pelajaran, karena kursus tidak ditemukan',
                            'status' => 'STATUS_NOT_FOUND',
                            'code' => HttpResponse::HTTP_NOT_FOUND,
                        ]
                    ])
                );
            }

            // Authorization with detailed logging
            $this->logger->info('Checking authorization with policy viewAllByCourse', [
                'user_id' => $user->id,
                'course_id' => $courseId,
            ]);

            if (!$this->gate->allows('viewAllByCourse', $course)) {
                $this->logger->error('Failed authorization process - user not authorized to view course lessons', [
                    'user_id' => $user->id,
                    'course_id' => $courseId,
                ]);

                throw new HttpResponseException(
                    $this->errorResponse([
                        [
                            'title' => 'Gagal mendapatkan semua pelajaran',
                            'details' => 'Gagal mendapatkan semua pelajaran, karena user tidak diizinkan melakukan aksi ini',
                            'code' => HttpResponse::HTTP_FORBIDDEN,
                            'status' => 'STATUS_FORBIDDEN',
                        ]
                    ])
                );
            }

            $this->logger->info('Authorization successful - starting the process of getting all lessons', [
                'user_id' => $user->id,
                'course_id' => $courseId
            ]);

            // Get pagination parameters
            $page = $data->validated('page', 1);
            $size = $data->validated('size', 10);

            // Retrieve paginated lessons
            $lessons = $this->lesson->newQuery()
                ->where('course_id', $courseId)
                ->paginate(perPage: $size, page: $page);

            $this->logger->info('Successfully retrieved all lessons', [
                'total' => $lessons->total(),
                'page' => $lessons->currentPage(),
                'size' => $lessons->perPage(),
                'course_id' => $courseId
            ]);

            return LessonResource::collection($lessons);

        } catch (\Exception $exception) {
            $this->logger->error('Failed to get all lessons: ' . $exception->getMessage(), [
                'exception_class' => get_class($exception),
                'trace' => $exception->getTraceAsString(),
                'course_id' => $courseId
            ]);

            if ($exception instanceof HttpResponseException) {
                throw $exception;
            }

            throw new HttpResponseException($this->errorResponse([
                [
                    'title' => 'Gagal mendapatkan semua pelajaran',
                    'details' => 'Gagal mendapatkan semua pelajaran karena kegagalan server, untuk selengkapnya di meta properti',
                    'code' => HttpResponse::HTTP_INTERNAL_SERVER_ERROR,
                    'status' => 'STATUS_INTERNAL_SERVER_ERROR',
                    'meta' => [
                        'en' => [
                            'error' => $exception->getMessage()
                        ]
                    ]
                ]
            ]));
        }
    }
    public function show(string $courseId, string $lessonId): LessonResource
    {
        try {
            // authentication
            $this->logger->info('before starting the course show process, the authentication and authorization process first');
            $user = $this->authGuard->user();
            if (!$user) {
                $this->logger->error('failed authentication process will return a response');
                throw new HttpResponseException(
                    $this->errorResponse([
                        [
                            'title' => 'Gagal mendapatkan pelajaran',
                            'details' => 'gagal mendapatkan pelajaran karena user tidak terautentikasi',
                            'code' => HttpResponse::HTTP_UNAUTHORIZED,
                            'status' => 'STATUS_UNAUTHORIZED',
                        ]
                    ])
                );
            }


            $course = $this->course->newQuery()->find($courseId);
            if (!$course) {
                $this->logger->error('Failed to show lesson: the course not found', [
                    'course_id' => $courseId
                ]);

                throw new HttpResponseException(
                    $this->errorResponse([
                        [
                            'title' => 'Gagal mendapatkan pelajaran',
                            'details' => 'Gagal mendapatkan pelajaran, karena kursus tidak ditemukan',
                            'status' => 'STATUS_NOT_FOUND',
                            'code' => HttpResponse::HTTP_NOT_FOUND,
                        ]
                    ])
                );
            }


            $lesson =  $this->lesson->newQuery()->whereBelongsTo($course,'course')->find($lessonId);
            if (!$lesson) {
                $this->logger->error('failed to show the lesson: lesson was not found');
                throw new HttpResponseException(
                    $this->errorResponse([
                        [
                            'title' => 'Gagal mendapatkan pelajaran',
                            'details' => 'gagal mendapatkan pelajaran karena pelajaran tidak ditemukan',
                            'code' => HttpResponse::HTTP_NOT_FOUND,
                            'status' => 'STATUS_NOT_FOUND',
                        ]
                    ])
                );
            }

            // authorization
            if(!$this->gate->allows('view', $lesson)) {
                $this->logger->error('failed authorization process will return a response');
                throw new HttpResponseException(
                    $this->errorResponse([
                        [
                            'title' => 'Gagal mendapatkan pelajaran',
                            'details' => 'gagal mendapatkan pelajaran, karena user tidak diizinkan melakukan aksi ini',
                            'code' => HttpResponse::HTTP_FORBIDDEN,
                            'status' => 'STATUS_FORBIDDEN',
                        ]
                    ])
                );
            }
            $this->logger->info('return result show the lesson to resource');
            return new LessonResource($lesson);
        } catch (ModelNotFoundException $exception) {
            $this->logger->error('failed to show the lesson: lesson was not found', [
                'error' => $exception->getMessage()
            ]);
            throw new HttpResponseException(
                $this->errorResponse([
                    [
                        'title' => 'Gagal mendapatkan pelajaran',
                        'details' => 'gagal mendapatkan pelajaran karena pelajaran tidak ditemukan',
                        'code' => HttpResponse::HTTP_NOT_FOUND,
                        'status' => 'STATUS_NOT_FOUND',
                    ]
                ])
            );
        }
    }

    public function create(StoreLessonRequest $data, string $courseId): LessonResource
    {
        try {
            // authentication
            $this->logger->info('before starting the course creation process, the authentication and authorization process first');
            $user = $this->authGuard->user();
            if (!$user) {
                $this->logger->error('failed authentication process will return a response');
                throw new HttpResponseException(
                    $this->errorResponse([
                        [
                            'title' => 'Gagal membuat pelajaran',
                            'details' => 'gagal membuat pelajaran karena user tidak terautentikasi',
                            'code' => HttpResponse::HTTP_UNAUTHORIZED,
                            'status' => 'STATUS_UNAUTHORIZED',
                        ]
                    ])
                );
            }

            if (!$this->course->newQuery()->where('id', $courseId)->exists()) {
                $this->logger->error('Failed to create lesson: the course not found', [
                    'course_id' => $courseId
                ]);

                throw new HttpResponseException(
                    $this->errorResponse([
                        [
                            'title' => 'Gagal membuat pelajaran',
                            'details' => 'Gagal membuat pelajaran, karena kursus tidak ditemukan',
                            'status' => 'STATUS_NOT_FOUND',
                            'code' => HttpResponse::HTTP_NOT_FOUND,
                        ]
                    ])
                );
            }


            // authorization
            if(!$this->gate->allows('create', Lesson::class)) {
                $this->logger->error('failed authorization process will return a response');
                throw new HttpResponseException(
                    $this->errorResponse([
                        [
                            'title' => 'Gagal membuat pelajaran',
                            'details' => 'gagal membuat pelajaran, karena user tidak diizinkan melakukan aksi ini',
                            'code' => HttpResponse::HTTP_FORBIDDEN,
                            'status' => 'STATUS_FORBIDDEN',
                        ]
                    ])
                );
            }
            // validate request
            $this->logger->info('after authentication and authorization, next start the request validation process');
            $validated = $data->validated();
            $this->logger->info('successfully pass the request validation process, then create a lesson.');
            // create
            $lesson = $this->lesson->newQuery()->create([
                'title' =>  $validated['title'],
                'description' => $validated['description'],
                'course_id' => $courseId,
                'video_link' => $validated['video_link'],
                'duration' => $validated['duration'],
                'order_number' => $validated['order_number'],
            ]);
            return new LessonResource($lesson);
        } catch (MissingAttributeException $exception) {
            $this->logger->error('failed to create the lesson: missing attribute: ' . $exception->getMessage());
            throw new HttpResponseException($this->errorResponse([
                [
                    'title' => 'Gagal membuat pelajaran',
                    'details' => 'atribut yang diperlukan hilang untuk detail nya di meta properti',
                    'code' => HttpResponse::HTTP_BAD_REQUEST,
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

    public function update(UpdateLessonRequest $data, string $courseId, string $lessonId): LessonResource
    {
        try {
            // authentication
            $this->logger->info('before starting the course update process, the authentication and authorization process first');
            $user = $this->authGuard->user();
            if (!$user) {
                $this->logger->error('failed authentication process will return a response');
                throw new HttpResponseException(
                    $this->errorResponse([
                        [
                            'title' => 'Gagal memperbarui pelajaran',
                            'details' => 'gagal memperbarui pelajaran karena user tidak terautentikasi',
                            'code' => HttpResponse::HTTP_UNAUTHORIZED,
                            'status' => 'STATUS_UNAUTHORIZED',
                        ]
                    ])
                );
            }

            if (!$this->course->newQuery()->where('id', $courseId)->exists()) {
                $this->logger->error('Failed to update lesson: the course not found', [
                    'course_id' => $courseId
                ]);

                throw new HttpResponseException(
                    $this->errorResponse([
                        [
                            'title' => 'Gagal memperbarui pelajaran',
                            'details' => 'Gagal memperbarui pelajaran, karena kursus tidak ditemukan',
                            'status' => 'STATUS_NOT_FOUND',
                            'code' => HttpResponse::HTTP_NOT_FOUND,
                        ]
                    ])
                );
            }


            $lesson =  $this->lesson->newQuery()->find($lessonId);
            if (!$lesson) {
                $this->logger->error('failed to update the lesson: lesson was not found');
                throw new HttpResponseException(
                    $this->errorResponse([
                        [
                            'title' => 'Gagal memperbarui pelajaran',
                            'details' => 'gagal memperbarui pelajaran karena pelajaran tidak ditemukan',
                            'code' => HttpResponse::HTTP_NOT_FOUND,
                            'status' => 'STATUS_NOT_FOUND',
                        ]
                    ])
                );
            }
            // authorization
            if(!$this->gate->allows('update', $lesson)) {
                $this->logger->error('failed authorization process will return a response');
                throw new HttpResponseException(
                    $this->errorResponse([
                        [
                            'title' => 'Gagal memperbarui pelajaran',
                            'details' => 'gagal memperbarui pelajaran, karena user tidak diizinkan melakukan aksi ini',
                            'code' => HttpResponse::HTTP_FORBIDDEN,
                            'status' => 'STATUS_FORBIDDEN',
                        ]
                    ])
                );
            }
            // validate request
            $this->logger->info('after authentication and authorization, next start the request validation process');
            $validated = $data->validated();
            $this->logger->info('successfully pass the request validation process, then update a lesson.');

            $updatedFields = [];
            foreach ($validated as $key => $value) {
                if (isset($value)) {
                    $updatedFields[] = $key;
                    $lesson->{$key} = $value;
                }
            }
            $lesson->save();

            $this->logger->info('successfully updated a lesson with updated fields', [
                'updated_fields' => $updatedFields
            ]);

            return new LessonResource($lesson);
        } catch (MissingAttributeException $exception) {
            $this->logger->error('failed to update the lesson: missing attribute: ' . $exception->getMessage());
            throw new HttpResponseException($this->errorResponse([
                [
                    'title' => 'Gagal memperbarui pelajaran',
                    'details' => 'atribut yang diperlukan hilang untuk detail nya di meta properti',
                    'code' => HttpResponse::HTTP_BAD_REQUEST,
                    'status' => 'STATUS_BAD_REQUEST',
                    'meta' => [
                        'en' => [
                            'error' => $exception->getMessage(),
                        ]
                    ]
                ]
            ]));
        };
    }

    public function delete(string $courseId, string $lessonId): array
    {
        try {
            // authentication
            $this->logger->info('before starting the course delete process, the authentication and authorization process first');
            $user = $this->authGuard->user();
            if (!$user) {
                $this->logger->error('failed authentication process will return a response');
                throw new HttpResponseException(
                    $this->errorResponse([
                        [
                            'title' => 'Gagal menghapus pelajaran',
                            'details' => 'gagal menghapus pelajaran karena user tidak terautentikasi',
                            'code' => HttpResponse::HTTP_UNAUTHORIZED,
                            'status' => 'STATUS_UNAUTHORIZED',
                        ]
                    ])
                );
            }

            if (!$this->course->newQuery()->where('id', $courseId)->exists()) {
                $this->logger->error('Failed to delete lesson: the course not found', [
                    'course_id' => $courseId
                ]);

                throw new HttpResponseException(
                    $this->errorResponse([
                        [
                            'title' => 'Gagal menghapus pelajaran',
                            'details' => 'Gagal menghapus pelajaran, karena kursus tidak ditemukan',
                            'status' => 'STATUS_NOT_FOUND',
                            'code' => HttpResponse::HTTP_NOT_FOUND,
                        ]
                    ])
                );
            }


            $lesson =  $this->lesson->newQuery()->find($lessonId);
            if (!$lesson) {
                $this->logger->error('failed to delete the lesson: lesson was not found');
                throw new HttpResponseException(
                    $this->errorResponse([
                        [
                            'title' => 'Gagal delete pelajaran',
                            'details' => 'gagal delete pelajaran karena pelajaran tidak ditemukan',
                            'code' => HttpResponse::HTTP_NOT_FOUND,
                            'status' => 'STATUS_NOT_FOUND',
                        ]
                    ])
                );
            }
            // authorization
            if(!$this->gate->allows('delete', $lesson)) {
                $this->logger->error('failed authorization process will return a response');
                throw new HttpResponseException(
                    $this->errorResponse([
                        [
                            'title' => 'Gagal menghapus pelajaran',
                            'details' => 'gagal menghapus pelajaran, karena user tidak diizinkan melakukan aksi ini',
                            'code' => HttpResponse::HTTP_FORBIDDEN,
                            'status' => 'STATUS_FORBIDDEN',
                        ]
                    ])
                );
            }
            // delete lessons
            $lesson->delete();
            return [
                'mentor_id' => $user->id,
                'deleted_lesson_id' => $lessonId
            ];
        } catch (ModelNotFoundException $exception) {
            $this->logger->error('failed to delete the lesson: lesson was not found');
            throw new HttpResponseException(
                $this->errorResponse([
                    [
                        'title' => 'Gagal delete pelajaran',
                        'details' => 'gagal delete pelajaran karena pelajaran tidak ditemukan',
                        'code' => HttpResponse::HTTP_NOT_FOUND,
                        'status' => 'STATUS_NOT_FOUND',
                    ]
                ])
            );
        };
    }


}
