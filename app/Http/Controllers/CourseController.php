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
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class CourseController extends Controller implements HasMiddleware
{
    use HttpResponses;
    public function __construct(
        private  readonly  CourseService $courseService,
        protected Logger $logger
    )
    {

    }

    /**
     * Display a listing of the resource.
     */
    public function index(PaginationRequest $request) : JsonResponse
    {
        try {
            $this->logger->info('processing request for get all courses');
            $courses = $this->courseService->getAll($request);
            $this->logger->info('successfully retrieved all courses');

            return $this->successResponse(
                [
                    'title' => 'Successfully Get All Courses',
                    'code' => 200,
                    'status' => HttpResponse::HTTP_OK,
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
            throw new HttpResponseException($this->errorInternalToResponse($exception, 'Courses Retrieval Failed'));
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCourseRequest $request) : JsonResponse
    {
        try {
            $this->logger->info('processing request for create course');
            $course = $this->courseService->create($request);
            $this->logger->info('successfully created course');

            return $this->successResponse(
                [
                    'title' => 'Successfully Create Courses',
                    'code' => 200,
                    'status' => HttpResponse::HTTP_OK,
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
            throw new HttpResponseException($this->errorInternalToResponse($exception, 'Course Create Failed'));
        }
    }

    /**
     * Display the specified resource.
     */
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
                    'title' => 'Successfully Show Courses',
                    'code' => 200,
                    'status' => HttpResponse::HTTP_OK,
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
            throw new HttpResponseException($this->errorInternalToResponse($exception, 'Course Show Failed'));
        }
    }

    /**
     * Update the specified resource in storage.
     */
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
                    'title' => 'Successfully Update Courses',
                    'code' => 200,
                    'status' => HttpResponse::HTTP_OK,
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
            throw new HttpResponseException($this->errorInternalToResponse($exception, 'Course Update Failed'));
        }
    }

    /**
     * Remove the specified resource from storage.
     */
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
                    'title' => 'Successfully Delete Courses',
                    'code' => 200,
                    'status' => HttpResponse::HTTP_OK,
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
            throw new HttpResponseException($this->errorInternalToResponse($exception, 'Course Delete Failed'));
        }
    }

    public static function middleware() : array
    {
        return [ new Middleware('auth:api') ];
    }
}
