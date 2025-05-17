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
use Symfony\Component\HttpFoundation\Response;

class PaymentController extends Controller implements HasMiddleware
{
    use HttpResponses;
    public function __construct(
        private readonly PaymentService $paymentService,
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
            $this->logger->info('Retrieving payment list', [
                'user_id' => auth()->id(),
                'pagination' => $request->validated()
            ]);

            $payments = $this->paymentService->index($request);
            return $this->successResponse([
                'title' => 'Berhasil mendapatkan semua pembayaran berdasarkan penomoran halaman',
                'code' => Response::HTTP_OK,
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

    /**
     * Store a newly created resource in storage.
     *
     * @param StorePaymentRequest $request
     * @return JsonResponse
     */
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
                'code' =>  Response::HTTP_CREATED,
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

    /**
     * Display the specified resource.
     *
     * @param string $id
     * @return JsonResponse
     */
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
                'code' =>  Response::HTTP_OK,
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

    /**
     * Update the specified resource in storage.
     *
     * @param UpdatePaymentRequest $request
     * @param string $id
     * @return JsonResponse
     */
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
                'code' =>  Response::HTTP_OK,
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

    /**
     * Remove the specified resource from storage.
     *
     * @param string $id
     * @return JsonResponse
     */
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
                'code' =>  Response::HTTP_OK,
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
