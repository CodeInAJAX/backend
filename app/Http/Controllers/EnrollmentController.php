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
use Symfony\Component\HttpFoundation\Response;

class EnrollmentController extends Controller implements HasMiddleware
{
    use HttpResponses;
    public function __construct(
        private readonly EnrollmentService $enrollmentService,
        protected Logger $logger
    )
    {

    }

    /**
     * Display a listing of the resource.
     *
     * @param PaginationRequest $request
     * @return JsonResponse
     */
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
                'code' => Response::HTTP_OK,
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

    /**
     * Display the specified resource.
     *
     * @param string $id
     * @return JsonResponse
     */
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
                'code' => Response::HTTP_OK,
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

    /**
     * Remove the specified resource from storage.
     *
     * @param string $id
     * @return JsonResponse
     */
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
                'code' => Response::HTTP_OK,
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
