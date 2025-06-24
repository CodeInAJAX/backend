<?php

namespace App\Service\Implements;

use App\Enums\Status;
use App\Enums\StatusPayment;
use App\Http\Requests\PaginationRequest;
use App\Http\Requests\StorePaymentRequest;
use App\Http\Requests\UpdatePaymentRequest;
use App\Http\Resources\PaymentResource;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Service\Contracts\PaymentService;
use App\Traits\HttpResponses;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Database\Eloquent\MissingAttributeException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Log\Logger;
use Tymon\JWTAuth\JWTGuard;

class PaymentServiceImpl implements PaymentService
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
            $this->logger->info("start the get all payment process, first authenticate");

            $user = $this->authGuard->user();
            if (!$user) {
                $this->logger->error('failed get all payment: user is not authenticated');
                throw new HttpResponseException($this->errorResponse([
                    [
                        'title' => 'Gagal mendapatkan semua pembayaran berdasarkan penomoran halaman',
                        'details' => 'gagal mendapatkan semua pembayaran berdasarkan penomoran halaman, karena user tidak terautentikasi',
                        'code' => HttpResponse::HTTP_UNAUTHORIZED,
                        'status' => 'STATUS_UNAUTHORIZED',
                    ]
                ]));
            }
            // validate the request
            $this->logger->info('successfully passed the authentication process, then the validation process with user id', [
                'user_id' => $user->id,
            ]);

            // Get pagination parameters
            $page = $data->validated('page', 1);
            $size = $data->validated('size', 10);

            $payments = $this->payment->newQuery()->whereBelongsTo($user, 'user')->with('course')->paginate(perPage: $size, page: $page);

            $this->logger->info('successfully passed the validation process then the authorization process');
            // authorization
            if (!$this->gate->allows('viewAll', [$this->payment, $payments->getCollection()])) {
                $this->logger->error('failed to pass the authorization process with user id', [
                    'user_id' => $user->id
                ]);
                throw new HttpResponseException($this->errorResponse([
                    [
                        'title' => 'Gagal mendapatkan semua pembayaran berdasarkan penomoran halaman',
                        'details' => 'gagal mendapatkan semua pembayaran berdasarkan penomoran halaman, karena user tidak diizinkan melakukan aksi ini',
                        'code' => HttpResponse::HTTP_FORBIDDEN,
                        'status' => 'STATUS_FORBIDDEN',
                    ]
                ]));
            }

            $this->logger->info('successfully passed the authorization process then the return result ');


            return PaymentResource::collection($payments);
        } catch (\Exception $exception) {
            $this->logger->error('Failed to get all payments: ' . $exception->getMessage(), [
                'exception_class' => get_class($exception),
                'trace' => $exception->getTraceAsString(),
            ]);

            if ($exception instanceof HttpResponseException) {
                throw $exception;
            }

            throw new HttpResponseException($this->errorResponse([
                [
                    'title' => 'Gagal mendapatkan semua pembayaran berdasarkan penomoran halaman',
                    'details' => 'gagal mendapatkan semua pembayaran berdasarkan penomoran halaman, karena kegagalan server, untuk selengkapnya di meta properti',
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

    public function show(string $id): PaymentResource
    {
        try {
            // authentication
            $this->logger->info("start the payment show process, first authenticate");

            $user = $this->authGuard->user();
            if (!$user) {
                $this->logger->error('failed payment show: user is not authenticated');
                throw new HttpResponseException($this->errorResponse([
                    [
                        'title' => 'Gagal mendapatkan detail pembayaran',
                        'details' => 'gagal mendapatkan detail pembayaran karena user tidak terautentikasi',
                        'code' => HttpResponse::HTTP_UNAUTHORIZED,
                        'status' => 'STATUS_UNAUTHORIZED',
                    ]
                ]));
            }
            // validate the request
            $this->logger->info('successfully passed the authentication process, then the validation process with user id', [
                'user_id' => $user->id,
            ]);

            $payment = $this->payment->newQuery()->with(['user', 'course'])->find($id);

            if (!$payment) {
                $this->logger->error('failed payment show: payment not found');
                throw new HttpResponseException($this->errorResponse([
                    [
                        'title' => 'Gagal mendapatkan detail pembayaran',
                        'details' => 'gagal mendapatkan detail pembayaran karena pembayaran tidak ditemukan',
                        'code' => HttpResponse::HTTP_NOT_FOUND,
                        'status' => 'STATUS_NOT_FOUND',
                    ]
                ]));
            }

            $this->logger->info('successfully passed the validation process then the authorization process');
            // authorization
            if (!$this->gate->allows('view', $payment)) {
                $this->logger->error('failed to pass the authorization process with user id', [
                    'user_id' => $user->id
                ]);
                throw new HttpResponseException($this->errorResponse([
                    [
                        'title' => 'Gagal mendapatkan detail pembayaran',
                        'details' => 'gagal mendapatkan detail pembayaran karena user tidak diizinkan melakukan aksi ini',
                        'code' => HttpResponse::HTTP_FORBIDDEN,
                        'status' => 'STATUS_FORBIDDEN',
                    ]
                ]));
            }

            $this->logger->info('successfully passed the authorization process then the return result ');


            return new PaymentResource($payment);
        } catch (ModelNotFoundException $exception) {
            $this->logger->error('failed to show the payment: payment was not found: ' . $exception->getMessage());
            throw new HttpResponseException($this->errorResponse([
                [
                    'title' => 'Gagal mendapatkan detail pembayaran',
                    'details' => 'gagal mendapatkan detail pembayaran karena pembayaran tidak ditemukan',
                    'code' => HttpResponse::HTTP_NOT_FOUND,
                    'status' => 'STATUS_NOT_FOUND',
                ]
            ]));
        }
    }

    public function update(UpdatePaymentRequest $data, string $id): PaymentResource
    {
        try {
            // authentication
            $this->logger->info("start the payment update process, first authenticate");

            $user = $this->authGuard->user();
            if (!$user) {
                $this->logger->error('failed payment update: user is not authenticated');
                throw new HttpResponseException($this->errorResponse([
                    [
                        'title' => 'Gagal memperbarui pembayaran',
                        'details' => 'gagal memperbarui pembayaran karena user tidak terautentikasi',
                        'code' => HttpResponse::HTTP_UNAUTHORIZED,
                        'status' => 'STATUS_UNAUTHORIZED',
                    ]
                ]));
            }
            // validate the request
            $this->logger->info('successfully passed the authentication process, then the validation process with user id', [
                'user_id' => $user->id,
            ]);

            $payment = $this->payment->newQuery()->find($id);

            if (!$payment) {
                $this->logger->error('failed payment update: payment not found');
                throw new HttpResponseException($this->errorResponse([
                    [
                        'title' => 'Gagal memperbarui pembayaran',
                        'details' => 'gagal memperbarui pembayaran karena pembayaran tidak ditemukan',
                        'code' => HttpResponse::HTTP_NOT_FOUND,
                        'status' => 'STATUS_NOT_FOUND',
                    ]
                ]));
            }


            $validated = $data->validated();
            $this->logger->info('successfully passed the validation process then the authorization process');

            $enrollment = $this->enrollment->newQuery()->where('student_id', $payment->user_id)->where('course_id', $payment->course_id)->first();
            if (!$enrollment) {
                $this->logger->warning('failed payment update: enrollment not found');
                throw new HttpResponseException($this->errorResponse([
                    [
                        'title' => 'Gagal memperbarui pembayaran',
                        'details' => 'gagal memperbarui pembayaran, karena data enrollment tidak ada, anda dapat menghapus pembayaran dan melakukan pembayaran lagi',
                        'code' => HttpResponse::HTTP_UNPROCESSABLE_ENTITY,
                        'status' => 'STATUS_UNPROCESSABLE_ENTITY',
                    ]
                ]));
            }

            // authorization
            if (!$this->gate->allows('update', $payment) && !$this->gate->allows('update', $enrollment)) {
                $this->logger->error('failed to pass the authorization process with user id', [
                    'user_id' => $user->id
                ]);
                throw new HttpResponseException($this->errorResponse([
                    [
                        'title' => 'Gagal memperbarui pembayaran',
                        'details' => 'gagal memperbarui pembayaran karena user tidak diizinkan melakukan aksi ini',
                        'code' => HttpResponse::HTTP_FORBIDDEN,
                        'status' => 'STATUS_FORBIDDEN',
                    ]
                ]));
            }

            $this->logger->info('successfully passed the authorization process then the payment update process ');

            $updatedFields = [];

            foreach ($validated as $key => $value) {
                if (isset($value)) {
                    $updatedFields[] = $key;
                    $payment->$key = $value;
                }
            }

            $payment = DB::transaction(function () use ($payment,$enrollment) {
                $payment->save();
                $enrollment->status = match ($payment->status) {
                    StatusPayment::SUCCESS => Status::ACTIVE,
                    StatusPayment::PENDING, StatusPayment::FAILED => Status::PENDING
                };
                $enrollment->save();
                return $payment;
            });

            $this->logger->info('successfully passed the payment update process with updated fields', [
                'updated_fields' => $updatedFields
            ]);

            return new PaymentResource($payment);
        } catch (MissingAttributeException $exception) {
            $this->logger->error('failed to update the payment: missing attribute: ' . $exception->getMessage());
            throw new HttpResponseException($this->errorResponse([
                [
                    'title' => 'Gagal memperbarui pembayaran',
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

    public function delete(string $id): array
    {
        try {
            // authentication
            $this->logger->info("start the payment delete process, first authenticate");

            $user = $this->authGuard->user();
            if (!$user) {
                $this->logger->error('failed payment delete: user is not authenticated');
                throw new HttpResponseException($this->errorResponse([
                    [
                        'title' => 'Gagal menghapus pembayaran',
                        'details' => 'gagal menghapus pembayaran karena user tidak terautentikasi',
                        'code' => HttpResponse::HTTP_UNAUTHORIZED,
                        'status' => 'STATUS_UNAUTHORIZED',
                    ]
                ]));
            }
            // validate the request
            $this->logger->info('successfully passed the authentication process, then the validation process with user id', [
                'user_id' => $user->id,
            ]);

            $payment = $this->payment->newQuery()->find($id);

            if (!$payment) {
                $this->logger->error('failed payment delete: payment not found');
                throw new HttpResponseException($this->errorResponse([
                    [
                        'title' => 'Gagal menghapus pembayaran',
                        'details' => 'gagal menghapus pembayaran karena pembayaran tidak ditemukan',
                        'code' => HttpResponse::HTTP_NOT_FOUND,
                        'status' => 'STATUS_NOT_FOUND',
                    ]
                ]));
            }

            $enrollment = $this->enrollment->newQuery()->where('student_id', $payment->user_id)->where('course_id', $payment->course_id)->first();
            if (!$enrollment) {
                $this->logger->warning('failed payment delete: enrollment not found with data', [
                    'user_id' => $payment->user_id,
                    'course_id' => $payment->course_id,
                    'payment_id' => $payment->id,
                ]);
            }

            $this->logger->info('successfully passed the validation process then the authorization process');
            // authorization
            $canDeleteEnrollment = !$enrollment || $this->gate->allows('delete', $enrollment);

            if (!$this->gate->allows('delete', $payment) && !$canDeleteEnrollment) {
                $this->logger->error('failed to pass the authorization process with user id', [
                    'user_id' => $user->id
                ]);
                throw new HttpResponseException($this->errorResponse([
                    [
                        'title' => 'Gagal menghapus pembayaran',
                        'details' => 'gagal menghapus pembayaran karena user tidak diizinkan melakukan aksi ini',
                        'code' => HttpResponse::HTTP_FORBIDDEN,
                        'status' => 'STATUS_FORBIDDEN',
                    ]
                ]));
            }

            $this->logger->info('successfully passed the authorization process then the return result ');

            $enrollmentId = DB::transaction(function () use ($payment, $enrollment) {
                $payment->delete();
                $enrollmentId = null;

                if (!empty($enrollment)) {
                    $enrollmentId = $enrollment->id;
                    $enrollment->delete();
                }

                return $enrollmentId;
            });

            $response = [
                'user_id' => $user->id,
                'deleted_payment_id' => $id,
            ];

            if (!empty($enrollmentId)) {
                $response['deleted_enrollment_id'] = $enrollmentId;
            }

            return $response;

        } catch (ModelNotFoundException $exception) {
            $this->logger->error('failed to show the payment: payment was not found: ' . $exception->getMessage());
            throw new HttpResponseException($this->errorResponse([
                [
                    'title' => 'Gagal menghapus pembayaran',
                    'details' => 'gagal menghapus pembayaran karena pembayaran tidak ditemukan',
                    'code' => HttpResponse::HTTP_NOT_FOUND,
                    'status' => 'STATUS_NOT_FOUND',
                ]
            ]));
        }
    }

    public function create(StorePaymentRequest $data): PaymentResource
    {
        try {

            // authentication
            $this->logger->info("start the payment creation process, first authenticate");

            $user = $this->authGuard->user();
            if (!$user) {
                $this->logger->error('failed payment creation: user is not authenticated');
                throw new HttpResponseException($this->errorResponse([
                    [
                        'title' => 'Gagal membuat pembayaran',
                        'details' => 'gagal membuat pembayaran karena user tidak terautentikasi',
                        'code' => HttpResponse::HTTP_UNAUTHORIZED,
                        'status' => 'STATUS_UNAUTHORIZED',
                    ]
                ]));
            }
            // validate the request
            $this->logger->info('successfully passed the authentication process, then the validation process with user id', [
                'user_id' => $user->id,
            ]);
            $validated = $data->validated();
            $this->logger->info('successfully passed the validation process then the authorization process');
            // authorization
            if (!$this->gate->allows('create', Payment::class) && !$this->gate->allows('create', Enrollment::class)) {
                $this->logger->error('failed to pass the authorization process with user id', [
                    'user_id' => $user->id
                ]);
                throw new HttpResponseException($this->errorResponse([
                    [
                        'title' => 'Gagal membuat pembayaran',
                        'details' => 'gagal membuat pembayaran karena user tidak diizinkan melakukan aksi ini',
                        'code' => HttpResponse::HTTP_FORBIDDEN,
                        'status' => 'STATUS_FORBIDDEN',
                    ]
                ]));
            }

            $this->logger->info('successfully passed the authorization process then the payment creation process ');
            $payment = DB::transaction(function () use ($validated, $user) {
                $payment = $this->payment->newQuery()->create([
                    'user_id' => $user->id,
                    'course_id' => $validated['course_id'],
                    'amount' => $validated['amount'],
                    'currency' => $validated['currency'],
                    'payment_method' => $validated['payment_method'],
                ]);

                $this->enrollment->newQuery()->create(
                    [
                        'student_id' => $user->id,
                        'course_id' => $validated['course_id'],
                        'enrolled_at' => now(),
                    ]
                );

                return $payment;
            });

            return new PaymentResource($payment);
        } catch (MissingAttributeException $exception) {
            $this->logger->error('failed to create the payment: missing attribute: ' . $exception->getMessage());
            throw new HttpResponseException($this->errorResponse([
                [
                    'title' => 'Gagal membuat pembayaran',
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
