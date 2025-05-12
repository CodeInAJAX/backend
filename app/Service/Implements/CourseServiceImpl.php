<?php

namespace App\Service\Implements;

use App\Http\Requests\PaginationRequest;
use App\Http\Requests\StoreCourseRequest;
use App\Http\Requests\UpdateCourseRequest;
use App\Http\Resources\CourseResource;
use App\Models\Course;
use App\Service\Contracts\CourseService;
use App\Traits\HttpResponses;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Database\Eloquent\MissingAttributeException;
//use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Log\Logger;
use Tymon\JWTAuth\JWTGuard;

class CourseServiceImpl implements CourseService
{
    use HttpResponses;
    protected StatefulGuard|Guard|JWTGuard $authGuard;
    /**
     * Create a new class instance.
     */
    public function __construct(
        protected Course $course,
        protected Logger $logger,
        AuthFactory $authFactory,
        protected Gate $gate,
    )
    {
        $this->authGuard = $authFactory->guard('api');
    }


    public function getAll(PaginationRequest $data): AnonymousResourceCollection
    {
        try {
            $this->logger->info('starts the authentication process before getting all courses');
            // authentication the users
            $user = $this->authGuard->user();
            if (!$user) {
                $this->logger->error('failed the authentication process before getting all courses');
                throw new HttpResponseException($this->errorResponse([
                    [
                        'title' => 'Course List Failed',
                        'details' => 'failed to get courses: the user not authenticated',
                        'code' => 401,
                        'status' => 'STATUS_UNAUTHORIZED',
                    ]
                ]));
            }
            $this->logger->info('start the process of getting all courses with user id', [
                'user_id' => $user->id
            ]);

            // get pagination parameters
            $page = $data->validated('page', 1);
            $size = $data->validated('size', 10);

            // query courses with relations
            $courses = $this->course->newModelQuery()
                ->withExists(['mentor', 'lessons'])
                ->paginate($size, ['*'], 'page', $page);

            $this->logger->info('successfully retrieved all courses', [
                'total' => $courses->total(),
                'page' => $courses->currentPage(),
                'size' => $courses->perPage(),
            ]);

            return CourseResource::collection($courses);
        } catch (\Exception $exception) {

            $this->logger->error('failed to get all courses: ' . $exception->getMessage());
            if ( $exception instanceof HttpResponseException ) {
                throw $exception;
            }
            throw new HttpResponseException($this->errorResponse([
                [
                    'title' => 'Course List Failed',
                    'details' => 'failed to get courses: ' . $exception->getMessage(),
                    'code' => 500,
                    'status' => 'INTERNAL_SERVER_ERROR'
                ]
            ]));
        }
    }

    public function show(string $id): CourseResource
    {
        try {
            $this->logger->info('starts the authentication process before show a course');
            // authentication the users
            $user = $this->authGuard->user();
            if (!$user) {
                $this->logger->error('failed the authentication process before show the course');
                throw new HttpResponseException($this->errorResponse([
                    [
                        'title' => 'Course Show Failed',
                        'details' => 'failed to show course: the user not authenticated',
                        'code' => 401,
                        'status' => 'STATUS_UNAUTHORIZED',
                    ]
                ]));
            }
            $this->logger->info('start the process of show a course with user id', [
                'user_id' => $user->id
            ]);

            // validate request
            $course = $this->course->newModelQuery()->withExists(['mentor','lessons'])->find($id);
            if (!$course) {
                $this->logger->error('failed to show the course: the course not found');
                throw new HttpResponseException($this->errorResponse([
                    [
                        'title' => 'Course Show Failed',
                        'details' => 'failed to show course: the course not found',
                        'code' => 404,
                        'status' => 'STATUS_NOT_FOUND'
                    ]
                ]));
            }

            $course->refresh();
            $this->logger->info('successfully show a course, then return course creation results');

            return new CourseResource($course);
        } catch (ModelNotFoundException $exception) {
            $this->logger->error('failed to show the course: course not found ' . $exception->getMessage());
            throw new HttpResponseException($this->errorResponse([
                [
                    'title' => 'Course Show Failed',
                    'details' => 'course not found: ' . $exception->getMessage(),
                    'code' => 404,
                    'status' => 'STATUS_NOT_FOUND'
                ]
            ]));
        }
    }

    public function create(StoreCourseRequest $data): CourseResource
    {
        try {
            $this->logger->info('starts the authentication process before creating a course');
            // authentication the users
            $user = $this->authGuard->user();
            if (!$user) {
                $this->logger->error('failed the authentication process before creating the course');
                throw new HttpResponseException($this->errorResponse([
                    [
                        'title' => 'Course Create Failed',
                        'details' => 'failed to create course: the user not authenticated',
                        'code' => 401,
                        'status' => 'STATUS_UNAUTHORIZED',
                    ]
                ]));
            }
            $this->logger->info('start the process of creating a course with user id', [
                'user_id' => $user->id
            ]);
            // validate request
            $validated = $data->validated();
            $this->logger->info('successfully pass the validation process before creating a course');
            // authorize the user with gate allows
            if (!$this->gate->allows('create', Course::class)) {
                $this->logger->error('failed the authorization process before creating the course');
                throw new HttpResponseException($this->errorResponse([
                    [
                        'title' => 'Course Create Failed',
                        'details' => 'failed to create course: the user not unauthorized to this action',
                        'code' => 403,
                        'status' => 'STATUS_FORBIDDEN',
                    ]
                ]));
            }
            $this->logger->info('start course creation with user id', [
                'user_id' => $user->id
            ]);
            // create course and return
            $course = $this->course->newQuery()->create([
                'title' => $validated['title'],
                'description' => $validated['description'],
                'price' => $validated['price'],
                'currency' => $validated['currency'],
                'thumbnail' => $validated['thumbnail'],
                'mentor_id' => $user->id
            ]);
            $this->logger->info('successfully created a course, then return course creation results');
            return new CourseResource($course);
        } catch (MissingAttributeException $exception) {
            $this->logger->error('failed to create the course: missing attribute: ' . $exception->getMessage());
            throw new HttpResponseException($this->errorResponse([
                [
                    'title' => 'Course Create Failed',
                    'details' => 'missing required attribute: ' . $exception->getMessage(),
                    'code' => 400,
                    'status' => 'STATUS_BAD_REQUEST'
                ]
            ]));
        }
    }

    public function update(string $id, UpdateCourseRequest $data): CourseResource
    {
        try {
            $this->logger->info('starts the authentication process before updating a course');
            // authentication the users
            $user = $this->authGuard->user();
            if (!$user) {
                $this->logger->error('failed the authentication process before updating the course');
                throw new HttpResponseException($this->errorResponse([
                    [
                        'title' => 'Course Update Failed',
                        'details' => 'failed to update course: the user not authenticated',
                        'code' => 401,
                        'status' => 'STATUS_UNAUTHORIZED',
                    ]
                ]));
            }
            $this->logger->info('start the process of updating a course with user id', [
                'user_id' => $user->id
            ]);

            // validate request
            $course = $this->course->newQuery()->find($id);
            if (!$course) {
                $this->logger->error('failed to update the course: the course not found');
                throw new HttpResponseException($this->errorResponse([
                    [
                        'title' => 'Course Update Failed',
                        'details' => 'failed to update course: the course not found',
                        'code' => 404,
                        'status' => 'STATUS_NOT_FOUND'
                    ]
                ]));
            }

            $validated = $data->validated();
            $this->logger->info('successfully pass the validation process before updating a course');
            // authorize the user with gate allows
            if (!$this->gate->allows('update', $course)) {
                $this->logger->error('failed the authorization process before updating the course');
                throw new HttpResponseException($this->errorResponse([
                    [
                        'title' => 'Course Update Failed',
                        'details' => 'failed to update course: the user not unauthorized to this action',
                        'code' => 403,
                        'status' => 'STATUS_FORBIDDEN',
                    ]
                ]));
            }
            $this->logger->info('successfully passed authorization, proceeding with update');
            $updatedFields = [];
            foreach ($validated as $key => $value) {
                if (isset($value)) {
                    $updatedFields[] = $key;
                    $course->{$key} = $value;
                }
            }

            $course->save();
            $course = $this->course->newModelQuery()->withExists(['mentor','lessons'])->find($id);
            $this->logger->info('successfully updated a course with updated fields', [
                'updated_fields' => $updatedFields
            ]);
            // logic the update course and return
            return new CourseResource($course);

        } catch (MissingAttributeException $exception) {
            $this->logger->error('failed to update the course: missing attribute: ' . $exception->getMessage());
            throw new HttpResponseException($this->errorResponse([
                [
                    'title' => 'Course Update Failed',
                    'details' => 'missing required attribute: ' . $exception->getMessage(),
                    'code' => 400,
                    'status' => 'STATUS_BAD_REQUEST'
                ]
            ]));
        }
    }
    public function delete(string $id): array
    {
        try {
            $this->logger->info('starts the authentication process before delete a course');
            // authentication the users
            $user = $this->authGuard->user();
            if (!$user) {
                $this->logger->error('failed the authentication process before delete the course');
                throw new HttpResponseException($this->errorResponse([
                    [
                        'title' => 'Course Delete Failed',
                        'details' => 'failed to delete course: the user not authenticated',
                        'code' => 401,
                        'status' => 'STATUS_UNAUTHORIZED',
                    ]
                ]));
            }
            $this->logger->info('start the process of deleting a course with user id', [
                'user_id' => $user->id
            ]);

            // validate request
            $course = $this->course->newQuery()->find( $id);
            if (!$course) {
                $this->logger->error('failed to delete the course: the course not found');
                throw new HttpResponseException($this->errorResponse([
                    'title' => 'Course Delete Failed',
                    'details' => 'failed to delete course: the course not found',
                    'code' => 404,
                    'status' => 'STATUS_NOT_FOUND'
                ]));
            }

            // authorize the user with gate allows
            if (!$this->gate->allows('delete', $course)) {
                $this->logger->error('failed the authorization process before delete the course');
                throw new HttpResponseException($this->errorResponse([
                    [
                        'title' => 'Course Delete Failed',
                        'details' => 'failed to update course: the user not unauthorized to this action',
                        'code' => 403,
                        'status' => 'STATUS_FORBIDDEN',
                    ]
                ]));
            }

            $course->delete();
            $this->logger->info('successfully delete the course with id', [
                'course_id' => $id,
                'mentor_id' => $user->id
            ]);
            return [
                'course_id' => $id,
                'mentor_id' => $user->id
            ];
        } catch (\Exception $exception) {
            $this->logger->error('failed to delete the course: ' . $exception->getMessage());
            if ( $exception instanceof HttpResponseException ) {
                throw $exception;
            }
            throw new HttpResponseException($this->errorResponse([
                [
                    'title' => 'Course Delete Failed',
                    'details' => 'failed to delete course: ' . $exception->getMessage(),
                    'code' => 500,
                    'status' => 'INTERNAL_SERVER_ERROR'
                ]
            ]));
        }
    }
}
