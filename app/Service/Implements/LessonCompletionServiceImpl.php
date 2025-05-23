<?php

namespace App\Service\Implements;

use App\Http\Requests\StoreLessonCompletionRequest;
use App\Http\Requests\UpdateLessonCompletionRequest;
use App\Http\Resources\LessonCompletionResource;
use App\Models\Lesson;
use App\Models\LessonCompletion;
use App\Service\Contracts\LessonCompletionService;
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

class LessonCompletionServiceImpl implements LessonCompletionService
{
    use HttpResponses;
    protected Guard|StatefulGuard|JWTGuard $authGuard;
    /**
     * Create a new class instance.
     */
    public function __construct(
        protected LessonCompletion $lessonCompletion,
        protected Lesson $lesson,
        protected Logger $logger,
        AuthFactory $authFactory,
        protected Gate $gate,
    )
    {
        $this->authGuard = $authFactory->guard('api');
    }

    public function show(string $lessonCompletionId): LessonCompletionResource
    {
        try {
            $this->logger->info('start the process of show detail lesson completion ');
            // authentication
            $user = $this->authGuard->user();
            if (!$user) {
                $this->logger->info('failed process authentication, user not authenticated');
                throw new HttpResponseException(
                    $this->errorResponse([
                        [
                            'title' => 'Gagal mendapatkan detail penyelesaian pelajaran',
                            'details' => 'Gagal mendapatkan detail penyelesaian pelajaran, karena user tidak terautentikasi',
                            'code' => HttpResponse::HTTP_UNAUTHORIZED,
                            'status' => 'STATUS_UNAUTHORIZED',
                        ]
                    ])
                );
            }

            $this->logger->info('succeed authentication process, start request validation process');

            // validate the request
            $lessonCompletion = $this->lessonCompletion->newQuery()->with(['student', 'lesson'])->find($lessonCompletionId);
            if (!$lessonCompletion) {
                $this->logger->error('failed to show detail the lesson completion: lesson was not found');
                throw new HttpResponseException(
                    $this->errorResponse([
                        [
                            'title' => 'Gagal mendapatkan detail penyelesaian pelajaran ',
                            'details' => 'gagal mendapatkan detail penyelesaian pelajaran  karena penyelesaian pelajaran tidak ditemukan',
                            'code' => HttpResponse::HTTP_NOT_FOUND,
                            'status' => 'STATUS_NOT_FOUND',
                        ]
                    ])
                );
            }
            // authorization
            $this->logger->info('start authorization process for permission to show detail lesson completion');
            if (!$this->gate->allows('view', $lessonCompletion)) {
                $this->logger->info('failed process authorization, user not authenticated');
                throw new HttpResponseException(
                    $this->errorResponse([
                        [
                            'title' => 'Gagal mendapatkan detail penyelesaian pelajaran',
                            'details' => 'Gagal mendapatkan detail penyelesaian pelajaran, karena user tidak diizinkan melakukan aksi ini',
                            'code' => HttpResponse::HTTP_UNAUTHORIZED,
                            'status' => 'STATUS_UNAUTHORIZED',
                        ]
                    ])
                );
            }


            $this->logger->info('successfully show detail lesson completion and will return the result');
            return new LessonCompletionResource($lessonCompletion);
        } catch (ModelNotFoundException $exception) {
            $this->logger->error('failed to show detail the lesson completion: lesson completion not found: ' . $exception->getMessage());
            throw new HttpResponseException($this->errorResponse([
                [
                    'title' => 'Gagal mendapatkan detail penyelesaian pelajaran',
                    'details' => 'gagal mendapatkan detail penyelesaian pelajaran, karena penyelesaian pelajaran tidak ditemukan',
                    'code' => HttpResponse::HTTP_NOT_FOUND,
                    'status' => 'STATUS_NOT_FOUND',
                    'meta' => [
                        'en' => [
                            'error' => $exception->getMessage(),
                        ]
                    ]
                ]
            ]));
        }
    }

    public function create(StoreLessonCompletionRequest $data, string $lessonId): LessonCompletionResource
    {
        try {
            $this->logger->info('start the process of creating lesson completion ');
            // authentication
            $user = $this->authGuard->user();
            if (!$user) {
                $this->logger->info('failed process authentication, user not authenticated');
                throw new HttpResponseException(
                    $this->errorResponse([
                        [
                            'title' => 'Gagal membuat penyelesaian pelajaran',
                            'details' => 'Gagal membuat penyelesaian pelajaran, karena user tidak terautentikasi',
                            'code' => HttpResponse::HTTP_UNAUTHORIZED,
                            'status' => 'STATUS_UNAUTHORIZED',
                        ]
                    ])
                );
            }

            $this->logger->info('succeed authentication process, start request validation process');

            // validate the request
            $validated = $data->validated();
            $lesson = $this->lesson->newQuery()->find($lessonId);
            if (!$lesson) {
                $this->logger->error('failed to create the lesson completion: lesson was not found');
                throw new HttpResponseException(
                    $this->errorResponse([
                        [
                            'title' => 'Gagal membuat penyelesaian pelajaran',
                            'details' => 'gagal membuat penyelesaian pelajaran karena pelajaran tidak ditemukan',
                            'code' => HttpResponse::HTTP_NOT_FOUND,
                            'status' => 'STATUS_NOT_FOUND',
                        ]
                    ])
                );
            }
            // authorization
            $this->logger->info('start authorization process for permission to create lesson completion');
            if (!$this->gate->allows('createLessonCompletion', $lesson)) {
                $this->logger->info('failed process authorization, user not authenticated');
                throw new HttpResponseException(
                    $this->errorResponse([
                        [
                            'title' => 'Gagal membuat penyelesaian pelajaran',
                            'details' => 'Gagal membuat penyelesaian pelajaran, karena user tidak diizinkan melakukan aksi ini',
                            'code' => HttpResponse::HTTP_UNAUTHORIZED,
                            'status' => 'STATUS_UNAUTHORIZED',
                        ]
                    ])
                );
            }

            $lessonCompletion = $lesson->lessonsCompletions()->create([
                'lesson_id' => $lessonId,
                'student_id' => $user->id,
                'watch_duration' => $validated['watch_duration'],
                'completed_at' => now(),
            ]);
            $this->logger->info('successfully created lesson completion and will return the result');
            return new LessonCompletionResource($lessonCompletion);
        } catch (MissingAttributeException $exception) {
            $this->logger->error('failed to create the lesson completion: missing attribute: ' . $exception->getMessage());
            throw new HttpResponseException($this->errorResponse([
                [
                    'title' => 'Gagal membuat penyelesaian pelajaran',
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

    public function update(UpdateLessonCompletionRequest $data, string $lessonCompletionId): LessonCompletionResource
    {
        try {
            $this->logger->info('start the process of updating lesson completion ');
            // authentication
            $user = $this->authGuard->user();
            if (!$user) {
                $this->logger->info('failed process authentication, user not authenticated');
                throw new HttpResponseException(
                    $this->errorResponse([
                        [
                            'title' => 'Gagal memperbarui penyelesaian pelajaran',
                            'details' => 'Gagal memperbarui penyelesaian pelajaran, karena user tidak terautentikasi',
                            'code' => HttpResponse::HTTP_UNAUTHORIZED,
                            'status' => 'STATUS_UNAUTHORIZED',
                        ]
                    ])
                );
            }

            $this->logger->info('succeed authentication process, start request validation process');

            // validate the request
            $validated = $data->validated();
            $lessonCompletion = $this->lessonCompletion->newQuery()->find($lessonCompletionId);
            if (!$lessonCompletion) {
                $this->logger->error('failed to update the lesson completion: lesson completion was not found');
                throw new HttpResponseException(
                    $this->errorResponse([
                        [
                            'title' => 'Gagal memperbarui penyelesaian pelajaran ',
                            'details' => 'gagal memperbarui penyelesaian pelajaran  karena penyelesaian pelajaran tidak ditemukan',
                            'code' => HttpResponse::HTTP_NOT_FOUND,
                            'status' => 'STATUS_NOT_FOUND',
                        ]
                    ])
                );
            }
            // authorization
            $this->logger->info('start authorization process for permission to update lesson completion');
            if (!$this->gate->allows('update', $lessonCompletion)) {
                $this->logger->info('failed process authorization, user not authenticated');
                throw new HttpResponseException(
                    $this->errorResponse([
                        [
                            'title' => 'Gagal memperbarui penyelesaian pelajaran',
                            'details' => 'Gagal memperbarui penyelesaian pelajaran, karena user tidak diizinkan melakukan aksi ini',
                            'code' => HttpResponse::HTTP_UNAUTHORIZED,
                            'status' => 'STATUS_UNAUTHORIZED',
                        ]
                    ])
                );
            }

            if (isset($validated['watch_duration'])) {
                $lessonCompletion->watch_duration = $validated['watch_duration'];
                $lessonCompletion->completed_at = now();
                $lessonCompletion->save();
            }

            $this->logger->info('successfully updated lesson completion and will return the result');
            return new LessonCompletionResource($lessonCompletion);
        } catch (MissingAttributeException $exception) {
            $this->logger->error('failed to updated the lesson completion: missing attribute: ' . $exception->getMessage());
            throw new HttpResponseException($this->errorResponse([
                [
                    'title' => 'Gagal memperbarui penyelesaian pelajaran',
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

    public function delete(string $lessonCompletionId): array
    {
        try {
            $this->logger->info('start the process of delete lesson completion ');
            // authentication
            $user = $this->authGuard->user();
            if (!$user) {
                $this->logger->info('failed process authentication, user not authenticated');
                throw new HttpResponseException(
                    $this->errorResponse([
                        [
                            'title' => 'Gagal menghapus penyelesaian pelajaran',
                            'details' => 'Gagal menghapus penyelesaian pelajaran, karena user tidak terautentikasi',
                            'code' => HttpResponse::HTTP_UNAUTHORIZED,
                            'status' => 'STATUS_UNAUTHORIZED',
                        ]
                    ])
                );
            }


            // validate the request
            $lessonCompletion = $this->lessonCompletion->newQuery()->find($lessonCompletionId);
            if (!$lessonCompletion) {
                $this->logger->error('failed to delete the lesson completion: lesson was not found');
                throw new HttpResponseException(
                    $this->errorResponse([
                        [
                            'title' => 'Gagal menghapus penyelesaian pelajaran ',
                            'details' => 'gagal menghapus penyelesaian pelajaran  karena penyelesaian pelajaran tidak ditemukan',
                            'code' => HttpResponse::HTTP_NOT_FOUND,
                            'status' => 'STATUS_NOT_FOUND',
                        ]
                    ])
                );
            }
            // authorization
            $this->logger->info('start authorization process for permission to delete lesson completion');
            if (!$this->gate->allows('delete', $lessonCompletion)) {
                $this->logger->info('failed process authorization, user not authenticated');
                throw new HttpResponseException(
                    $this->errorResponse([
                        [
                            'title' => 'Gagal menghapus penyelesaian pelajaran',
                            'details' => 'Gagal menghapus penyelesaian pelajaran, karena user tidak diizinkan melakukan aksi ini',
                            'code' => HttpResponse::HTTP_UNAUTHORIZED,
                            'status' => 'STATUS_UNAUTHORIZED',
                        ]
                    ])
                );
            }

            $lessonCompletion->delete();

            $this->logger->info('successfully deleted lesson completion and will return the result');
            return [
                'user_id' => $user->id,
                'lesson_completion_id' => $lessonCompletionId,
            ];
        } catch (MissingAttributeException $exception) {
            $this->logger->error('failed to deleted the lesson completion: missing attribute: ' . $exception->getMessage());
            throw new HttpResponseException($this->errorResponse([
                [
                    'title' => 'Gagal menghapus penyelesaian pelajaran',
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
}
