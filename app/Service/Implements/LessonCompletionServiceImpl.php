<?php

namespace App\Service\Implements;

use App\Http\Requests\PaginationRequest;
use App\Http\Requests\StoreLessonCompletionRequest;
use App\Http\Requests\UpdateLessonCompletionRequest;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonCompletion;
use App\Service\Contracts\LessonCompletionService;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Database\Eloquent\MissingAttributeException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Log\Logger;
use Tymon\JWTAuth\JWTGuard;

class LessonCompletionServiceImpl implements LessonCompletionService
{
    protected Guard|StatefulGuard|JWTGuard $authGuard;
    /**
     * Create a new class instance.
     */
    public function __construct(
        protected LessonCompletion $lessonCompletion,
        protected Lesson $lesson,
        protected Course $course,
        protected Enrollment $enrollment,
        protected Logger $logger,
        AuthFactory $authFactory,
        protected Gate $gate,
    )
    {
        $this->authGuard = $authFactory->guard('api');
    }

    public function index(PaginationRequest $data): AnonymousResourceCollection
    {
        try {

        } catch (\Exception $exception) {

        }
    }

    public function show(string $id): LessonCompletion
    {
        // TODO: Implement show() method.
    }

    public function create(StoreLessonCompletionRequest $data): LessonCompletion
    {
        try {
            // authentication
            $user = $this->authGuard->user();
            if (!$user) {

            }

            // validate the request
            $validated = $data->validated();
            $lesson = $this->lesson->newQuery()->find($validated['lesson_id']);
            // authorization
            if (!$this->gate->allows('createLessonCompletion', $lesson)) {

            }

            $lessonCompletion = $lesson->lessonsCompletions()->create([
                'lesson_id' => $validated['lesson_id'],
                'student_id' => $user->id,
                'watch_duration' => $validated['watch_duration'],
                'completed_at' => now(),
            ]);

            return new LessonCompletion($lessonCompletion);
        } catch (MissingAttributeException $exception) {

        }
    }

    public function update(UpdateLessonCompletionRequest $data, string $id): LessonCompletion
    {
        // TODO: Implement update() method.
    }

    public function delete(string $id): array
    {
        // TODO: Implement delete() method.
    }
}
