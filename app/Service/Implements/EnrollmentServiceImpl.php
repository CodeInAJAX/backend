<?php

namespace App\Service\Implements;

use App\Enums\Status;
use App\Http\Requests\PaginationRequest;
use App\Http\Resources\EnrollmentResource;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Service\Contracts\EnrollmentService;
use App\Traits\HttpResponses;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Illuminate\Log\Logger;
use Tymon\JWTAuth\JWTGuard;

class EnrollmentServiceImpl implements EnrollmentService
{

    use HttpResponses;
    protected StatefulGuard|Guard|JWTGuard $authGuard;

    /**
     * Create a new class instance.
     */
    public function __construct(
        protected Payment $payment,
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
            // authentication
            $this->logger->info('starts the get all course enrollment process, starting with authentication.');
            $user = $this->authGuard->user();

            if (!$user) {
                $this->logger->error('failed the get all course enrollment process, because the user is not authenticated');
                throw new HttpResponseException($this->errorResponse([
                    [
                        'title' => 'Gagal mendapatkan pendaftaran kursus berdasarkan penomoran halaman',
                        'details' => 'gagal mendapatkan pendaftaran kursus berdasarkan penomoran halaman, karena user tidak terautentikasi',
                        'code' => HttpResponse::HTTP_UNAUTHORIZED,
                        'status' => 'STATUS_UNAUTHORIZED',
                    ]
                ]));
            }

            // validate the request
            // Get pagination parameters
            $page = $data->validated('page', 1);
            $size = $data->validated('size', 10);

            $this->logger->info('successfully get all enrollment course by pagination');

            $enrollments = $this->enrollment->newQuery()->whereBelongsTo($user, 'student')->with(['course','student'])->paginate(perPage: $size, page: $page);

            // authorization
            $this->logger->info('start authorization process for get all enrollment course by pagination');
            if(!$this->gate->allows('viewAll', [$this->enrollment, $enrollments->getCollection()])) {
                throw new HttpResponseException($this->errorResponse([
                    [
                        'title' => 'Gagal mendapatkan semua pendaftaran kursus berdasarkan penomoran halaman',
                        'details' => 'gagal mendapatkan semua pendaftaran kursus berdasarkan penomoran halaman, karena user tidak tidak diizinkan melakukan aksi ini',
                        'code' => HttpResponse::HTTP_FORBIDDEN,
                        'status' => 'STATUS_FORBIDDEN',
                    ]
                ]));
            }
            $this->logger->info('successfully get authorization get all enrollment course, return result');

            return EnrollmentResource::collection($enrollments);
        } catch (\Exception $exception) {
            $this->logger->error('Failed to get all enrollment course: ' . $exception->getMessage(), [
                'exception_class' => get_class($exception),
                'trace' => $exception->getTraceAsString(),
            ]);

            if ($exception instanceof HttpResponseException) {
                throw $exception;
            }

            throw new HttpResponseException($this->errorResponse([
                [
                    'title' => 'Gagal mendapatkan semua pendaftaran kursus berdasarkan penomoran halaman',
                    'details' => 'gagal mendapatkan semua pendaftaran kursus berdasarkan penomoran halaman, karena kegagalan server, untuk selengkapnya di meta properti',
                    'code' => HttpResponse::HTTP_INTERNAL_SERVER_ERROR,
                    'status' => 'INTERNAL_SERVER_ERROR',
                    'meta' => [
                        'en' => [
                            'error' => $exception->getMessage(),
                        ]
                    ]
                ]
            ]));
        }
    }

    public function show(string $id): EnrollmentResource
    {
        try {
            // authentication
            $this->logger->info('starts the show course enrollment process, starting with authentication.');
            $user = $this->authGuard->user();

            if (!$user) {
                $this->logger->error('failed the show course enrollment process, because the user is not authenticated');
                throw new HttpResponseException($this->errorResponse([
                    [
                        'title' => 'Gagal mendapatkan detail pendaftaran kursus',
                        'details' => 'gagal mendapatkan detail pendaftaran kursus, karena user tidak terautentikasi',
                        'code' => HttpResponse::HTTP_UNAUTHORIZED,
                        'status' => 'STATUS_UNAUTHORIZED',
                    ]
                ]));
            }

            // validate the enrollment
            $this->logger->info('start searching for enrollment course');
            $enrollment = $this->enrollment->newQuery()->with(['student','course'])->find($id);
            if (!$enrollment) {
                $this->logger->error('failed the enrollment course enrollment not found');
                throw new HttpResponseException($this->errorResponse([
                    [
                        'title' => 'Gagal mendapatkan detail pendaftaran kursus',
                        'details' => 'gagal mendapatkan detail pendaftaran kursus, karena penghapusan pendaftaran kursus tidak ditemukan',
                        'code' => HttpResponse::HTTP_NOT_FOUND,
                        'status' => 'STATUS_NOT_FOUND',
                    ]
                ]));
            }

            $this->logger->info('successfully get enrollment course');

            // authorization
            $this->logger->info('start authorization process for show detail enrollment course');
            if(!$this->gate->allows('view', $enrollment)) {
                throw new HttpResponseException($this->errorResponse([
                    [
                        'title' => 'Gagal mendapatkan detail pendaftaran kursus',
                        'details' => 'gagal mendapatkan detail pendaftaran kursus karena user tidak tidak diizinkan melakukan aksi ini',
                        'code' => HttpResponse::HTTP_FORBIDDEN,
                        'status' => 'STATUS_FORBIDDEN',
                    ]
                ]));
            }

            $this->logger->info('successfully get authorization show enrollment course, return result');

            return new EnrollmentResource($enrollment);
        } catch (ModelNotFoundException $exception) {
            $this->logger->error('failed to show the enrollment: enrollment was not found: ' . $exception->getMessage());
            throw new HttpResponseException($this->errorResponse([
                [
                    'title' => 'Gagal mendapatkan detail pendaftaran kursus',
                    'details' => 'gagal mendapatkan detail pendaftaran kursus, karena pendaftaran kursus tidak ditemukan',
                    'code' => HttpResponse::HTTP_NOT_FOUND,
                    'status' => 'STATUS_NOT_FOUND',
                ]
            ]));
        }
    }

    public function delete(string $id): array
    {
        try {
            // authentication
            $this->logger->info('starts the course enrollment deletion process, starting with authentication.');
            $user = $this->authGuard->user();

            if (!$user) {
               $this->logger->error('failed the course enrollment deletion process, because the user is not authenticated');
                throw new HttpResponseException($this->errorResponse([
                    [
                        'title' => 'Gagal penghapusan pendaftaran kursus',
                        'details' => 'gagal penghapusan pendaftaran kursus, karena user tidak terautentikasi',
                        'code' => HttpResponse::HTTP_UNAUTHORIZED,
                        'status' => 'STATUS_UNAUTHORIZED',
                    ]
                ]));
            }

            // validate the enrollment
            $this->logger->info('start searching for enrollment course');
            $enrollment = $this->enrollment->newQuery()->find($id);
            if (!$enrollment) {
                $this->logger->error('failed the enrollment course enrollment not found');
                throw new HttpResponseException($this->errorResponse([
                    [
                        'title' => 'Gagal penghapusan pendaftaran kursus',
                        'details' => 'gagal penghapusan pendaftaran kursus, karena penghapusan pendaftaran kursus tidak ditemukan',
                        'code' => HttpResponse::HTTP_NOT_FOUND,
                        'status' => 'STATUS_NOT_FOUND',
                    ]
                ]));
            }

            $this->logger->info('successfully get enrollment course');

            $payment = $this->payment->newQuery()->where('user_id', $enrollment->student_id)->where('course_id', $enrollment->course_id)->first();
            if (!$payment && $user->hasRole('student')) {
                $enrollment->status = Status::PENDING;
                $enrollment->save();
                $this->logger->error('failed the enrollment course, payment not found');
                throw new HttpResponseException($this->errorResponse([
                    [
                        'title' => 'Gagal penghapusan pendaftaran kursus',
                        'details' => 'gagal penghapusan pendaftaran kursus, karena tidak valid tanpa data rekaman pembayaran, tolong hubungi guru/mentor untuk memperbaiki nya',
                        'code' => HttpResponse::HTTP_CONFLICT,
                        'status' => 'STATUS_CONFLICT',
                    ]
                ]));
            }
            $canDeletePayment = !$payment || $this->gate->allows('delete', $payment);
            // authorization
            $this->logger->info('start authorization process for enrollment course deletion');
            if(!$canDeletePayment && !$this->gate->allows('delete', $enrollment)) {
                throw new HttpResponseException($this->errorResponse([
                    [
                        'title' => 'Gagal penghapusan pendaftaran kursus',
                        'details' => 'gagal penghapusan pendaftaran kursus karena user tidak tidak diizinkan melakukan aksi ini',
                        'code' => HttpResponse::HTTP_FORBIDDEN,
                        'status' => 'STATUS_FORBIDDEN',
                    ]
                ]));
            }

            // delete enrollment

            $paymentId = DB::transaction(function () use ($enrollment, $payment) {
                $enrollment->delete();
                $paymentId = null;

                if (!empty($payment)) {
                    $paymentId = $payment->id;
                    $payment->delete();
                }
                return $paymentId;
            });

            $response = [
                'user_id' => $user->id,
                'enrollment_id' => $id,
            ];

            if (!empty($paymentId)) {
                $response['payment_id'] = $paymentId;
            }
            return $response;
        } catch (ModelNotFoundException $exception) {
            $this->logger->error('failed to delete the enrollment: enrollment was not found: ' . $exception->getMessage());
            throw new HttpResponseException($this->errorResponse([
                [
                    'title' => 'Gagal menghapus pendaftaran kursus',
                    'details' => 'gagal menghapus pendaftaran kursus, karena pendaftaran kursus tidak ditemukan',
                    'code' => HttpResponse::HTTP_NOT_FOUND,
                    'status' => 'STATUS_NOT_FOUND',
                ]
            ]));
        }
    }
}
