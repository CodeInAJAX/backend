<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaginationRequest;
use App\Http\Requests\StorePaymentRequest;
use App\Http\Requests\UpdatePaymentRequest;
use App\Service\Contracts\PaymentService;
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
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Knuckles\Scribe\Attributes\Response;

#[Group("Payment Management", "APIs for managing payments")]
class PaymentController extends Controller implements HasMiddleware
{
    use HttpResponses;
    public function __construct(
        private readonly PaymentService $paymentService,
        protected Logger $logger
    )
    {

    }

    #[Endpoint('Get List Payments By Pagination', <<<DESC
  This endpoint allows you to get list payments by pagination.
  It's a really useful endpoint, because this endpoint can see all payments by pagination.
 DESC)]
    #[Authenticated(true)]
    #[Header('Accept', 'application/json')]
    #[Response(
        content:
        [
            "title" => "Berhasil mendapatkan semua pembayaran berdasarkan penomoran halaman",
            "status" => "STATUS_OK",
            "code" => 200,
            "meta" => [
                "current_page" => 1,
                "last_page" => 1,
                "per_page" => 10,
                "total" => 1
            ],
            "data" => [
                [
                    "id" => "01JV4V34JWQM8NQBJ23W68PVT4",
                    "userId" => "01JV4V07XVQPNJCW8TFCQ2JMV5",
                    "courseId" => "01JV4TVS0KSPNQ149H2C2ZCA8A",
                    "amount" => 50000,
                    "currency" => "idr",
                    "paymentMethod" => "cash",
                    "status" => "success",
                    "createdAt" => "2025-05-13T12:49:39.000000Z",
                    "updatedAt" => "2025-05-13T13:15:33.000000Z",
                    "course" => [
                        "id" => "01JV4TVS0KSPNQ149H2C2ZCA8A",
                        "title" => "Laravel 12",
                        "thumbnail" => "http://bailey.com/",
                        "description" => "Kursus Framework PHP Yang Populer dan Banyak digunakan Yaitu Laravel, Apalagi versi terbaru nya yaitu 12",
                        "price" => 50000,
                        "currency" => "idr",
                        "createdAt" => "2025-05-13T12:45:38.000000Z",
                        "updatedAt" => "2025-05-13T12:45:38.000000Z"
                    ]
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
                    'title' => 'Gagal mendapatkan semua pembayaran berdasarkan penomoran halaman',
                    'details' => 'Sesuatu ada yang salah, tolong coba lagi',
                    'code' => 500,
                    'status' => 'STATUS_INTERNAL_SERVER_ERROR'
                ]
            ]
        ],
        status: HttpResponse::HTTP_INTERNAL_SERVER_ERROR,
        description: 'Internal Server Error'
    )]
    public function index(PaginationRequest $request): JsonResponse
    {
        try {
            $this->logger->info('Retrieving payment list', [
                'user_id' => auth()->id(),
                'pagination' => $request->validated()
            ]);

            $payments = $this->paymentService->index($request);
            return $this->successResponse([
                'title' => 'Berhasil mendapatkan semua pembayaran berdasarkan penomoran halaman',
                'code' => HttpResponse::HTTP_OK,
                'status' => 'STATUS_OK',
                'data' => $payments->collection,
                'meta' => [
                    'current_page' => $payments->currentPage(),
                    'last_page' => $payments->lastPage(),
                    'per_page' => $payments->perPage(),
                    'total' => $payments->total(),
                ]
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Failed to retrieve payment list', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            if ($e instanceof HttpResponseException) throw $e;

            throw new HttpResponseException($this->errorInternalToResponse($e,'Gagal mendapatkan semua pembayaran berdasarkan penomoran halaman'));
        }
    }

    #[Endpoint('Create new Payments', <<<DESC
  This endpoint allows you to create new payments and auto create enrollments.
  It's a really useful endpoint, because this endpoint can create new payments.
 DESC)]
    #[Authenticated(true)]
    #[Header('Accept', 'application/json')]
    #[Response(
        content: [
            "title" => "Berhasil menambahkan pembayaran baru",
            "status" => "STATUS_CREATED",
            "code" => 201,
            "meta" => null,
            "data" => [
                "id" => "01JV4V34JWQM8NQBJ23W68PVT4",
                "userId" => "01JV4V07XVQPNJCW8TFCQ2JMV5",
                "courseId" => "01JV4TVS0KSPNQ149H2C2ZCA8A",
                "amount" => 50000,
                "currency" => "idr",
                "paymentMethod" => "cash",
                "status" => "pending",
                "createdAt" => "2025-05-13T12:49:39.000000Z",
                "updatedAt" => "2025-05-13T12:49:39.000000Z"
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
                    'title' => 'Gagal menambahkan pembayaran baru',
                    'details' => 'Sesuatu ada yang salah, tolong coba lagi',
                    'code' => 500,
                    'status' => 'STATUS_INTERNAL_SERVER_ERROR'
                ]
            ]
        ],
        status: HttpResponse::HTTP_INTERNAL_SERVER_ERROR,
        description: 'Internal Server Error'
    )]
    public function store(StorePaymentRequest $request): JsonResponse
    {
        try {
            $this->logger->info('Creating new payment', [
                'user_id' => auth()->id(),
                'data' => $request->validated()
            ]);

            $payment = $this->paymentService->create($request);

            $this->logger->info('Payment created successfully', [
                'user_id' => auth()->id(),
                'payment_id' => $payment->id
            ]);

            return $this->successResponse([
                'title' => 'Berhasil menambahkan pembayaran baru',
                'code' =>  HttpResponse::HTTP_CREATED,
                'status' => 'STATUS_CREATED',
                'data' => $payment,
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Failed to create payment', [
                'user_id' => auth()->id(),
                'data' => $request->validated(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            if ($e instanceof HttpResponseException) throw $e;

            throw new HttpResponseException($this->errorInternalToResponse($e,'Gagal membuat pembayaran'));
        }
    }

    #[Endpoint('Show Detail Payments', <<<DESC
  This endpoint allows you to show payment details.
  It's a really useful endpoint, because this endpoint can see payment details.
 DESC)]
    #[Authenticated(true)]
    #[Header('Accept', 'application/json')]
    #[Response(
        content:[
            "title" => "Berhasil mendapatkan detail pembayaran",
            "status" => "STATUS_OK",
            "code" => 200,
            "meta" => null,
            "data" => [
                "id" => "01JV4V34JWQM8NQBJ23W68PVT4",
                "userId" => "01JV4V07XVQPNJCW8TFCQ2JMV5",
                "courseId" => "01JV4TVS0KSPNQ149H2C2ZCA8A",
                "amount" => 50000,
                "currency" => "idr",
                "paymentMethod" => "cash",
                "status" => "success",
                "createdAt" => "2025-05-13T12:49:39.000000Z",
                "updatedAt" => "2025-05-13T13:15:33.000000Z",
                "user" => [
                    "id" => "01JV4V07XVQPNJCW8TFCQ2JMV5",
                    "name" => "pelajar",
                    "email" => "pelajar@gmail.com",
                    "role" => "student",
                    "profile" => [
                        "gender" => "male",
                        "about" => "architecto",
                        "photo" => "http://bailey.com/"
                    ],
                    "createdAt" => "2025-05-13T12:48:05.000000Z",
                    "updatedAt" => "2025-05-13T12:48:05.000000Z"
                ],
                "course" => [
                    "id" => "01JV4TVS0KSPNQ149H2C2ZCA8A",
                    "title" => "Laravel 12",
                    "thumbnail" => "http://bailey.com/",
                    "description" => "Kursus Framework PHP Yang Populer dan Banyak digunakan Yaitu Laravel, Apalagi versi terbaru nya yaitu 12",
                    "price" => 50000,
                    "currency" => "idr",
                    "createdAt" => "2025-05-13T12:45:38.000000Z",
                    "updatedAt" => "2025-05-13T12:45:38.000000Z"
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
                    'title' => 'Gagal mendapatkan detail pembayaran',
                    'details' => 'Sesuatu ada yang salah, tolong coba lagi',
                    'code' => 500,
                    'status' => 'STATUS_INTERNAL_SERVER_ERROR'
                ]
            ]
        ],
        status: HttpResponse::HTTP_INTERNAL_SERVER_ERROR,
        description: 'Internal Server Error'
    )]
    public function show(string $id): JsonResponse
    {
        try {
            $this->logger->info('Retrieving payment details', [
                'user_id' => auth()->id(),
                'payment_id' => $id
            ]);

            $payment = $this->paymentService->show($id);

            return $this->successResponse([
                'title' => 'Berhasil mendapatkan detail pembayaran',
                'code' =>  HttpResponse::HTTP_OK,
                'status' => 'STATUS_OK',
                'data' => $payment,
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Failed to retrieve payment details', [
                'user_id' => auth()->id(),
                'payment_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            if ($e instanceof HttpResponseException) throw $e;

            throw new HttpResponseException($this->errorInternalToResponse($e,'Gagal mendapatkan detail pembayaran'));
        }
    }


    #[Endpoint('Update Payments By ID', <<<DESC
  This endpoint allows you to update payments by id.
  It's a really useful endpoint, because this endpoint can edit payments by id.
 DESC)]
    #[Authenticated(true)]
    #[Header('Accept', 'application/json')]
    #[Response(
        content:  [
            "title" => "Berhasil memperbarui pembayaran",
            "status" => "STATUS_OK",
            "code" => 200,
            "meta" => null,
            "data" => [
                "id" => "01JV6DHQX986GBVJ1TASQRN772",
                "userId" => "01JV6DEXCJX7R0XRAZ08BC00T5",
                "courseId" => "01JV6DBAW1JQR7P11TZETWKB63",
                "amount" => 50000,
                "currency" => "IDR",
                "paymentMethod" => "cash",
                "status" => "success",
                "createdAt" => "2025-05-14T03:31:27.000000Z",
                "updatedAt" => "2025-05-14T03:32:53.000000Z",
                "course" => [
                    "id" => "01JV6DBAW1JQR7P11TZETWKB63",
                    "title" => "Laravel 12",
                    "thumbnail" => "http://bailey.com/",
                    "description" => "Eius et animi quos velit et.",
                    "price" => 50000,
                    "currency" => "IDR",
                    "createdAt" => "2025-05-14T03:27:57.000000Z",
                    "updatedAt" => "2025-05-14T03:27:57.000000Z"
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
                    'title' => 'Gagal memperbarui pembayaran',
                    'details' => 'Sesuatu ada yang salah, tolong coba lagi',
                    'code' => 500,
                    'status' => 'STATUS_INTERNAL_SERVER_ERROR'
                ]
            ]
        ],
        status: HttpResponse::HTTP_INTERNAL_SERVER_ERROR,
        description: 'Internal Server Error'
    )]
    public function update(UpdatePaymentRequest $request, string $id): JsonResponse
    {
        try {
            $this->logger->info('Updating payment', [
                'user_id' => auth()->id(),
                'payment_id' => $id,
                'data' => $request->validated()
            ]);

            $payment = $this->paymentService->update($request, $id);

            $this->logger->info('Payment updated successfully', [
                'user_id' => auth()->id(),
                'payment_id' => $id
            ]);

            return $this->successResponse([
                'title' => 'Berhasil memperbarui pembayaran',
                'code' =>  HttpResponse::HTTP_OK,
                'status' => 'STATUS_OK',
                'data' => $payment,
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Failed to update payment', [
                'user_id' => auth()->id(),
                'payment_id' => $id,
                'data' => $request->validated(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            if ($e instanceof HttpResponseException) throw $e;

            throw new HttpResponseException($this->errorInternalToResponse($e,'Gagal memperbarui pembayaran'));
        }
    }

        #[Endpoint('Delete Payments By ID', <<<DESC
  This endpoint allows you to delete payments by ID.
  It's a really useful endpoint, because this endpoint can delete payments by ID.
 DESC)]
    #[Authenticated(true)]
    #[Header('Accept', 'application/json')]
    #[Response(
        content: [
            "title" => "Berhasil menghapus pendaftaran kursus",
            "status" => "STATUS_OK",
            "code" => 200,
            "meta" => [
                "user_id" => "01JV4V07XVQPNJCW8TFCQ2JMV5",
                "enrollment_id" => "01JV4Y4ECPBFPX9DNN41AZB5BQ",
                "payment_id" => "01JV4Y4EC5WVV9728MGBXZE717"
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
        content: [
            'errors' => [
                [
                    'title' => 'Gagal menghapus pembayaran',
                    'details' => 'Sesuatu ada yang salah, tolong coba lagi',
                    'code' => 500,
                    'status' => 'STATUS_INTERNAL_SERVER_ERROR'
                ]
            ]
        ],
        status: HttpResponse::HTTP_INTERNAL_SERVER_ERROR,
        description: 'Internal Server Error'
    )]
    public function destroy(string $id): JsonResponse
    {
        try {
            $this->logger->info('Deleting payment', [
                'user_id' => auth()->id(),
                'payment_id' => $id
            ]);

            $result = $this->paymentService->delete($id);

            $this->logger->info('Payment deleted successfully', [
                'user_id' => auth()->id(),
                'payment_id' => $id
            ]);

            return $this->successResponse([
                'title' => 'Berhasil menghapus pembayaran',
                'code' =>  HttpResponse::HTTP_OK,
                'status' => 'STATUS_OK',
                'data' => null,
                'meta' => $result,
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Failed to delete payment', [
                'user_id' => auth()->id(),
                'payment_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            if ($e instanceof HttpResponseException) throw $e;

            throw new HttpResponseException($this->errorInternalToResponse($e,'Gagal menghapus pembayaran'));
        }
    }

    /**
     * Define middleware for this controller.
     *
     * @return array
     */
    public static function middleware(): array
    {
        return [new Middleware('auth:api')];
    }
}
