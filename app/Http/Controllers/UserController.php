<?php

namespace App\Http\Controllers;

use App\Http\Requests\FilterUserByEmailRequest;
use App\Http\Requests\LoginUserRequest;
use App\Http\Requests\PaginationRequest;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Log\Logger;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Header;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use App\Service\Contracts\UserService;
use App\Traits\HttpResponses;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\JWTGuard;

#[Group("Users management", "APIs for managing user accounts")]
class UserController extends Controller implements HasMiddleware
{
    use HttpResponses;
    protected Guard|StatefulGuard|JWTGuard $authGuard;
    public function __construct(
        private readonly UserService $userService,
        protected Logger             $logger,
    )
    {
    }

    public static function middleware(): array
    {
        return [
          new Middleware('auth:api', except: ['login', 'logout', 'refresh', 'store']),
        ];
    }


    #[Endpoint('Get List User By Pagination', <<<DESC
  This endpoint allows you to get list user by pagination.
  It's a really useful endpoint, because this endpoint can see all user by pagination.
 DESC)]
    #[Authenticated(authenticated: true)]
    #[Header('Accept', 'application/json')]
    #[Response(
        content: [
            'title' => 'Berhasil mendapatkan semua user berdasarkan penomoran halaman',
            'status' => 'STATUS_OK',
            'code' => 200,
            'meta' => [
                'current_page' => 1,
                'per_page' => 10,
                'total_users' => 100,
                'total_pages' => 10,
            ],
            'data' => [
                [
                    'id' => '01JTT6XDDMS8WE4WM7...',
                    'name' => 'johndoe',
                    'email' => 'johndoe@gmail.com',
                    'role' => 'student',
                    'profile' => [
                        'gender' => 'male',
                        'about' => 'lorem ipsum',
                        'photo' => 'http://bailey.com/'
                    ],
                    'createdAt' => '2025-05-09T09:44:36.000000Z',
                    'updatedAt' => '2025-05-09T09:44:36.000000Z'
                ],
                [
                    'id' => '01JTT6XDDMS8WE4WM7...',
                    'name' => 'foobar',
                    'email' => 'foobare@gmail.com',
                    'role' => 'student',
                    'profile' => [
                        'gender' => 'male',
                        'about' => 'lorem ipsum',
                        'photo' => 'http://bailey.com/'
                    ],
                    'createdAt' => '2025-05-09T09:44:36.000000Z',
                    'updatedAt' => '2025-05-09T09:44:36.000000Z'
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
                    'title' => 'User tidak terautentikasi',
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
                    'title' => 'Gagal mendapatkan semua user berdasarkan penomoran halaman',
                    'details' => 'Sesuatu ada yang salah, tolong coba lagi',
                    'code' => 500,
                    'status' => 'STATUS_INTERNAL_SERVER_ERROR'
                ]
            ]
        ],
        status: HttpResponse::HTTP_INTERNAL_SERVER_ERROR,
        description: 'Internal Server Error'
    )]
    public function index(PaginationRequest $request) : JsonResponse
    {
       try {
           $this->logger->info('receive request for get all users by pagination');

           $userCollection = $this->userService->getAll($request);
            return $this->successResponse([
               'title' => 'Berhasil mendapatkan semua user berdasarkan penomoran halaman',
               'status' => 'STATUS_OK',
               'code' => 200,
               'data' => $userCollection->collection,
               'meta' => [
                   'current_page' => $userCollection->currentPage(),
                   'last_page' => $userCollection->lastPage(),
                   'per_page' => $userCollection->perPage(),
                   'total' => $userCollection->total(),
               ]
           ]);
       } catch (\Exception $exception) {
           if ( $exception instanceof HttpResponseException ) {
               throw $exception;
           }
           $this->logger->error('failed processing request for get all users by pagination',  [
               'error' => $exception->getMessage()
           ]);
           throw new HttpResponseException($this->errorInternalToResponse($exception, 'Gagal mendapatkan semua user berdasarkan penomoran halaman'));
       }
    }

    #[Endpoint('Create New Users', <<<DESC
  This endpoint allows you to register a new user.
  It's a really useful endpoint, and you should play around
  with it for a bit.
 DESC)]
    #[Response(
        content: [
            'title' => 'Berhasil mendaftarkan user baru',
            'code' => 201,
            'status' => 'STATUS_CREATED',
            'data' => [
                'id' => '01JTJRCBKG6HHFFG6SFZDERQVF',
                'name' => 'johndoe',
                'email' => 'johndoe@gmail.com',
                'role' => 'student',
                'profile' => [
                    'gender' => 'male',
                    'about' => 'lorem ipsum',
                    'photo' => 'http://bailey.com/johndoe.jpg'
                ],
                'createdAt' => '2025-05-06T12:15:56.000000Z',
                'updatedAt' => '2025-05-06T12:15:56.000000Z'
            ],
        ],
        status: HttpResponse::HTTP_CREATED,
        description: 'Successfully'
    )]
    #[Response(
        content: [
            'errors' => [
                [
                    'title' => 'Gagal mengvalidasi permintaan',
                    'details' => [
                        'Email sudah digunakan.'
                    ],
                    'code' => 400,
                    'status' => 'STATUS_BAD_REQUEST'
                ]
            ]
        ],
        status: HttpResponse::HTTP_BAD_REQUEST,
        description: 'Bad Request'
    )]
    #[Response(
        content: [
            'errors' => [
                [
                    'title' => 'Gagal mendaftarkan user',
                    'details' => 'Sesuatu ada yang salah, tolong coba lagi',
                    'code' => 500,
                    'status' => 'STATUS_INTERNAL_SERVER_ERROR'
                ]
            ]
        ],
        status: HttpResponse::HTTP_INTERNAL_SERVER_ERROR,
        description: 'Internal Server Error'
    )]
    public function store(StoreUserRequest $request): JsonResponse
    {
        try {
            $this->logger->info('receive request for register users or create new user');

            $userRegistered = $this->userService->create($request);

            $this->logger->info('successfully processing request for register users or create new user');

            return $this->successResponse([
                'title' => 'Berhasil mendaftarkan user baru',
                'code' => HttpResponse::HTTP_CREATED,
                'status' => 'STATUS_CREATED',
                'data' => $userRegistered,
            ]);
        } catch (\Exception $exception) {
            if ( $exception instanceof HttpResponseException ) {
                throw $exception;
            }

            $this->logger->error('failed processing request for register users or create new user',  [
                'error' => $exception->getMessage()
            ]);

            throw new HttpResponseException($this->errorInternalToResponse($exception, 'Gagal mendaftarkan user'));
        }
    }

    #[Endpoint('Login Existing User', <<<DESC
  This endpoint allows you to login a user.
  It's a really useful endpoint, because this endpoint result access token to access protected route
 DESC)]
    #[Response(
        content: [
            [
                'title' => 'Berhasil login user',
                'status' => 'STATUS_OK',
                'code' => 200,
                'meta' => null,
                'data' => [
                    'access_token' => 'eyJ...',
                    'token_type' => 'Bearer',
                    'expires_in' => 86400
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
                    'title' => 'Gagal mengvalidasi permintaan',
                    'details' => [
                        'Konfirmasi kata sandi wajib diisi'
                    ],
                    'code' => 400,
                    'status' => 'STATUS_BAD_REQUEST'
                ]
            ]
        ],
        status: HttpResponse::HTTP_BAD_REQUEST,
        description: 'Bad Request'
    )]
    #[Response(
        content: [
            'errors' => [
                [
                    'title' => 'Gagal login user',
                    'details' => 'Sesuatu ada yang salah, tolong coba lagi',
                    'code' => 500,
                    'status' => 'STATUS_INTERNAL_SERVER_ERROR'
                ]
            ]
        ],
        status: HttpResponse::HTTP_INTERNAL_SERVER_ERROR,
        description: 'Internal Server Error'
    )]
    public function login(LoginUserRequest $request): JsonResponse
    {
        try {
            $this->logger->info('receive request for login users to get access token');

            $dataToken = $this->userService->login($request);
            $cookie = $this->userService->setCookieWithRefreshToken($dataToken['refresh_token']);
            unset($dataToken['refresh_token']);

            $this->logger->info('successfully request for login users to get access token');


            return $this->successResponse([
                'title' => 'Berhasil login user',
                'status' => 'STATUS_OK',
                'code' =>  200,
                'data' => $dataToken
            ])->withCookie($cookie);
        } catch (\Exception $exception) {
            if ( $exception instanceof HttpResponseException ) {
                throw $exception;
            }
            $this->logger->error('failed request for login users to get access token', [
                'error' => $exception->getMessage()
            ]);

            throw new HttpResponseException($this->errorInternalToResponse($exception, 'Gagal login user'));
        }
    }

    #[Endpoint('Refresh Token', <<<DESC
  This endpoint allows you to refresh access token to access protected route.
  It's a really useful endpoint, because this endpoint result access token to access protected route
 DESC)]
    #[Response(
        content: [
            [
                'title' => 'Berhasil menyegarkan token',
                'status' => 'STATUS_OK',
                'code' => 200,
                'meta' => null,
                'data' => [
                    'access_token' => 'eyJ...',
                    'token_type' => 'Bearer',
                    'expires_in' => 86400
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
                    'title' => 'Gagal menyegarkan token',
                    'details' => "'refresh_token' tidak tersedia di cookie, tolong coba lagi",
                    'code' => 400,
                    'status' => 'STATUS_BAD_REQUEST',
                ]
            ]
        ],
        status: HttpResponse::HTTP_BAD_REQUEST,
        description: 'Bad Request'
    )]
    #[Response(
        content: [
            'errors' => [
                [
                    'title' => 'Gagal menyegarkan token',
                    'details' => "tidak valid nilai dari kredensial 'refresh_token'",
                    'code' => 401,
                    'status' => 'STATUS_UNAUTHORIZED',
                ]
            ]
        ],
        status: HttpResponse::HTTP_UNAUTHORIZED,
        description: 'Unauthorized'
    )]
    #[Response(
        content: [
            'errors' => [
                [
                    'title' => 'Gagal menyegarkan token',
                    'details' => "'refresh_token' anda sudah kadaluarsa. anda dapat mendapatkan yang baru di login",
                    'code' => 403,
                    'status' => 'STATUS_FORBIDDEN',
                ]
            ]
        ],
        status: HttpResponse::HTTP_FORBIDDEN,
        description: 'Forbidden'
    )]
    #[Response(
        content: [
            'errors' => [
                [
                    'title' => 'Gagal menyegarkan token',
                    'details' => 'Sesuatu ada yang salah, tolong coba lagi',
                    'code' => 500,
                    'status' => 'STATUS_INTERNAL_SERVER_ERROR'
                ]
            ]
        ],
        status: HttpResponse::HTTP_INTERNAL_SERVER_ERROR,
        description: 'Internal Server Error'
    )]
    public function refresh(Request $request): JsonResponse
    {
        try {
            $this->logger->info('receive request for refresh token to get access token');

            $dataToken = $this->userService->refresh($request);

            $this->logger->info('successfully request for refresh token to get access token');


            return $this->successResponse([
                'title' => 'Berhasil menyegarkan token',
                'status' => 'STATUS_OK',
                'code' =>  200,
                'data' => $dataToken
            ]);
        } catch (\Exception $exception) {
            if ( $exception instanceof HttpResponseException ) {
                throw $exception;
            }
            $this->logger->error('failed request for refresh token to get access token', [
                'error' => $exception->getMessage()
            ]);

            throw new HttpResponseException($this->errorInternalToResponse($exception, 'Berhasil menyegarkan token'));
        }
    }

    #[Endpoint('Logout Existing User', <<<DESC
  This endpoint allows you to logout a user.
  It's a really useful endpoint, because this endpoint logout account user
 DESC)]
    #[Authenticated(authenticated: true)]
    #[Response(
        content:  [
            "title" => "Berhasil mengeluarkan akun user",
            "status" => "STATUS_OK",
            "code" => 200,
            "meta" => null,
            "data" => null
        ],
        status: HttpResponse::HTTP_OK,
        description: 'Successfully'
    )]
    #[Response(
        content: [
            'errors' => [
                [
                    'title' => 'Gagal mengeluarkan akun user',
                    'details' => 'Token anda sudah kadaluarsa, anda dapat menyegarkan kembali',
                    'status' => 'STATUS_FORBIDDEN',
                    'code' => 403,
                    'meta' => null
                ]
            ]
        ],status:  HttpResponse::HTTP_FORBIDDEN,
        description: 'Forbidden'
    )]
    #[Response(
        content: [
            'errors' => [
                [
                    'title' => 'Gagal mengeluarkan akun user',
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
                    'title' => 'Gagal mengeluarkan akun user',
                    'details' => 'Sesuatu ada yang salah, tolong coba lagi',
                    'code' => 500,
                    'status' => 'STATUS_INTERNAL_SERVER_ERROR'
                ]
            ]
        ],
        status: HttpResponse::HTTP_INTERNAL_SERVER_ERROR,
        description: 'Internal Server Error'
    )]
    public function logout(): JsonResponse
    {
        try {
            $this->logger->info('receive request for logout users to remove refresh token');

            Auth::guard('api')->logout();
            $cookie = cookie()->forget('refresh_token');

            $this->logger->info('successfully request for logout users to remove refresh token');

            return $this->successResponse([
                'title' => 'Berhasil mengeluarkan akun user',
                'status' => 'STATUS_OK',
                'code' =>  200,
                'data' => null
            ])->withCookie($cookie);

        } catch (TokenInvalidException | AuthenticationException $exception) {
            $this->logger->error('failed request for logout users to remove refresh token', [
                'errors' => $exception->getMessage()
            ]);
            throw new HttpResponseException($this->errorResponse([
                [
                    'title' => 'Gagal mengeluarkan akun user',
                    'details' => 'Kamu harus terautentikasi untuk melakukan aksi ini',
                    'code' => HttpResponse::HTTP_UNAUTHORIZED,
                    'status' => 'STATUS_UNAUTHORIZED',
                ]
            ]));
        } catch (TokenExpiredException $exception) {
            $this->logger->error('failed request for logout users to remove refresh token', [
                'errors' => $exception->getMessage()
            ]);
            throw new HttpResponseException($this->errorResponse([
                [
                    'title' => 'Gagal mengeluarkan akun user',
                    'details' => 'Token anda sudah kadaluarsa, anda dapat menyegarkan kembali',
                    'code' => HttpResponse::HTTP_FORBIDDEN,
                    'status' => 'STATUS_FORBIDDEN',
                ]
            ]));
        } catch (\Exception $exception) {
            if ( $exception instanceof HttpResponseException ) {
                throw $exception;
            }
            $this->logger->error('failed request for logout users to remove refresh token', [
                'error' => $exception->getMessage()
            ]);
            throw new HttpResponseException($this->errorInternalToResponse($exception, 'Gagal mengeluarkan akun user'));
        }
    }

    #[Endpoint('Get a User By Email', <<<DESC
  This endpoint allows you to get user by email.
  It's a really useful endpoint, because this endpoint can see other user by email
 DESC)]
    #[Authenticated(authenticated: true)]
    #[Response(
        content: [
            'title' => 'Berhasil mendapatkan user berdasarkan email',
            'status' => 'STATUS_OK',
            'code' => 200,
            'meta' => null,
            'data' => [
                'id' => '01JTT6XDDMS8WE4WM7...',
                'name' => 'johndoe',
                'email' => 'johndoe@gmail.com',
                'role' => 'student',
                'profile' => [
                    'gender' => 'male',
                    'about' => 'lorem ipsum',
                    'photo' => 'http://bailey.com/'
                ],
                'createdAt' => '2025-05-09T09:44:36.000000Z',
                'updatedAt' => '2025-05-09T09:44:36.000000Z'
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
                    'title' => 'Gagal mendapatkan user berdasarkan email',
                    'details' => 'Sesuatu ada yang salah, tolong coba lagi',
                    'code' => 500,
                    'status' => 'STATUS_INTERNAL_SERVER_ERROR'
                ]
            ]
        ],
        status: HttpResponse::HTTP_INTERNAL_SERVER_ERROR,
        description: 'Internal Server Error'
    )]
    public function show(FilterUserByEmailRequest $request): JsonResponse
    {
        try {
            $this->logger->info('receive request for show users to see a user');

            $user = $this->userService->getByEmail($request);

            $this->logger->info('successfully request for show users to see a user');

            return $this->successResponse([
                'title' => 'Berhasil mendapatkan user berdasarkan email',
                'code' => HttpResponse::HTTP_OK,
                'status' => 'STATUS_OK',
                'data' => $user,
            ]);

        } catch (\Exception $exception) {
            if ( $exception instanceof HttpResponseException ) {
                throw $exception;
            }
            $this->logger->error('failed request for show users to see a user',  [
                'error' => $exception->getMessage()
            ]);
            throw new HttpResponseException($this->errorInternalToResponse($exception, 'Gagal mendapatkan user berdasarkan email'));
        }
    }

    #[Endpoint('Get a Detail User By Authentication', <<<DESC
  This endpoint allows you to get user by authentication.
  It's a really useful endpoint, because this endpoint can see other user by authentication
 DESC)]
    #[Authenticated(authenticated: true)]
    #[Response(
        content: [
            'title' => 'Berhasil mendapatkan detail user',
            'status' => 'STATUS_OK',
            'code' => 200,
            'meta' => null,
            'data' => [
                'id' => '01JTT6XDDMS8WE4WM7...',
                'name' => 'johndoe',
                'email' => 'johndoe@gmail.com',
                'role' => 'student',
                'profile' => [
                    'gender' => 'male',
                    'about' => 'lorem ipsum',
                    'photo' => 'http://bailey.com/'
                ],
                'createdAt' => '2025-05-09T09:44:36.000000Z',
                'updatedAt' => '2025-05-09T09:44:36.000000Z'
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
                    'title' => 'Gagal mendapatkan detail user',
                    'details' => 'Sesuatu ada yang salah, tolong coba lagi',
                    'code' => 500,
                    'status' => 'STATUS_INTERNAL_SERVER_ERROR'
                ]
            ]
        ],
        status: HttpResponse::HTTP_INTERNAL_SERVER_ERROR,
        description: 'Internal Server Error'
    )]
    public function detail() :JsonResponse
    {
        try {
            $this->logger->info('receive request for show users to see a detail user');

            $user = $this->userService->show();

            $this->logger->info('successfully request for show users to see a detail user');

            return $this->successResponse([
                'title' => 'Berhasil mendapatkan detail user',
                'code' => HttpResponse::HTTP_OK,
                'status' => 'STATUS_OK',
                'data' => $user,
            ]);

        } catch (\Exception $exception) {
            if ( $exception instanceof HttpResponseException ) {
                throw $exception;
            }
            $this->logger->error('failed request for show detail a user',  [
                'error' => $exception->getMessage()
            ]);
            throw new HttpResponseException($this->errorInternalToResponse($exception, 'Gagal mendapatkan detail user'));
        }
    }

    #[Endpoint('Update a User By Authentication', <<<DESC
  This endpoint allows you to update a user by authentication.
  It's a really useful endpoint, because this endpoint can update data user
 DESC)]
    #[Authenticated(authenticated: true)]
    #[Response(
        content: [
            'title' => 'Berhasil memperbarui user',
            'status' => 'STATUS_OK',
            'code' => 200,
            'meta' => null,
            'data' => [
                'id' => '01JTT6XDDMS8WE4WM7...',
                'name' => 'johndoe',
                'email' => 'johndoe@gmail.com',
                'role' => 'student',
                'profile' => [
                    'gender' => 'male',
                    'about' => 'lorem ipsum',
                    'photo' => 'http://bailey.com/'
                ],
                'createdAt' => '2025-05-09T09:44:36.000000Z',
                'updatedAt' => '2025-05-09T09:44:36.000000Z'
            ]
        ],
        status: HttpResponse::HTTP_OK,
        description: 'Successfully'
    )]
    #[Response(
        content: [
            'errors' => [
                [
                    'title' => 'User tidak terautentikasi',
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
                    'title' => 'Gagal memperbarui user',
                    'details' => 'Sesuatu ada yang salah, tolong coba lagi',
                    'code' => 500,
                    'status' => 'STATUS_INTERNAL_SERVER_ERROR'
                ]
            ]
        ],
        status: HttpResponse::HTTP_INTERNAL_SERVER_ERROR,
        description: 'Internal Server Error'
    )]
    public function update(UpdateUserRequest $request) :JsonResponse
    {
        try {
            $this->logger->info('receive request for update user');

            $user = $this->userService->update($request);

            return $this->successResponse([
               'title' => 'Berhasil memperbarui user',
               'code' => HttpResponse::HTTP_OK,
               'status' => 'STATUS_OK',
                'data' => $user,
            ]);
        } catch (\Exception $exception) {
            if ( $exception instanceof HttpResponseException ) {
                throw $exception;
            }
            $this->logger->error('failed request for update user',  [
               'errors' => $exception->getMessage()
            ]);
            throw new HttpResponseException($this->errorInternalToResponse($exception, 'Gagal memperbarui user'));
        }
    }

    #[Endpoint('Delete Existing User', <<<DESC
  This endpoint allows you to delete a user.
  It's a really useful endpoint, because this endpoint soft delete a user (ADMIN) Only
 DESC)]
    #[Authenticated(authenticated: true)]
    #[Response(
        content:  [
            "title" => "Berhasil menghapus user",
            "status" => "STATUS_OK",
            "code" => 200,
            "meta" => [
                'admin_id' => '01JTT6XDDMS8WE4WM7...',
                'deleted_user_id' => '01JTT6XSSDS8WE4WM7...',
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
                    'title' => 'Gagal menghapus user',
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
            $this->logger->info('receive request for delete user');

            $meta = $this->userService->delete($id);

            return $this->successResponse([
                'title' => 'Berhasil menghapus user',
                'code' => HttpResponse::HTTP_OK,
                'status' => 'STATUS_OK',
                'data' => null,
                'meta' => $meta,
            ]);
        } catch (\Exception $exception) {
            if ( $exception instanceof HttpResponseException ) {
                throw $exception;
            }
            $this->logger->error('failed request for delete user',  [
                'errors' => $exception->getMessage()
            ]);
            throw new HttpResponseException($this->errorInternalToResponse($exception, 'Gagal menghapus user'));
        }
    }

    #[Endpoint('Restore Deleted User', <<<DESC
  This endpoint allows you to restore a user.
  It's a really useful endpoint, because this endpoint restore a user has been deleted
 DESC)]
    #[Authenticated(authenticated: true)]
    #[Response(
        content:  [
            "title" => "Berhasil mengembalikan user",
            "status" => "STATUS_OK",
            "code" => 200,
            "meta" => [
                'admin_id' => '01JTT6XDDMS8WE4WM7...',
                'restore_user_id' => '01JTT6XSSDS8WE4WM7...',
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
                    'title' => 'Gagal mengembalikan user',
                    'details' => 'Sesuatu ada yang salah, tolong coba lagi',
                    'code' => 500,
                    'status' => 'STATUS_INTERNAL_SERVER_ERROR'
                ]
            ]
        ],
        status: HttpResponse::HTTP_INTERNAL_SERVER_ERROR,
        description: 'Internal Server Error'
    )]
    public function restore(string $id): JsonResponse
    {
        try {
            $this->logger->info('receive request for restore user');

            $meta = $this->userService->restore($id);

            return $this->successResponse([
                'title' => 'Berhasil mengembalikan user',
                'code' => HttpResponse::HTTP_OK,
                'status' => 'STATUS_OK',
                'data' => null,
                'meta' => $meta,
            ]);
        } catch (\Exception $exception) {
            if ( $exception instanceof HttpResponseException ) {
                throw $exception;
            }
            $this->logger->error('failed request for restore user',  [
                'errors' => $exception->getMessage()
            ]);
            throw new HttpResponseException($this->errorInternalToResponse($exception, 'Gagal mengembalikan user'));
        }
    }

    #[Endpoint('Get List Trash User By Pagination', <<<DESC
  This endpoint allows you to get trash list user by pagination.
  It's a really useful endpoint, because this endpoint can see all trash user by pagination.
 DESC)]
    #[Authenticated(authenticated: true)]
    #[Header('Accept', 'application/json')]
    #[Response(
        content: [
            'title' => 'Berhasil mendapatkan semua user yang terhapus',
            'status' => 'STATUS_OK',
            'code' => 200,
            'meta' => [
                'current_page' => 1,
                'per_page' => 10,
                'total_users' => 100,
                'total_pages' => 10,
            ],
            'data' => [
                [
                    'id' => '01JTT6XDDMS8WE4WM7...',
                    'name' => 'johndoe',
                    'email' => 'johndoe@gmail.com',
                    'role' => 'student',
                    'profile' => [
                        'gender' => 'male',
                        'about' => 'lorem ipsum',
                        'photo' => 'http://bailey.com/'
                    ],
                    'createdAt' => '2025-05-09T09:44:36.000000Z',
                    'updatedAt' => '2025-05-09T09:44:36.000000Z'
                ],
                [
                    'id' => '01JTT6XDDMS8WE4WM7...',
                    'name' => 'foobar',
                    'email' => 'foobare@gmail.com',
                    'role' => 'student',
                    'profile' => [
                        'gender' => 'male',
                        'about' => 'lorem ipsum',
                        'photo' => 'http://bailey.com/'
                    ],
                    'createdAt' => '2025-05-09T09:44:36.000000Z',
                    'updatedAt' => '2025-05-09T09:44:36.000000Z'
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
                    'title' => 'Gagal mendapatkan semua user yang terhapus',
                    'details' => 'Sesuatu ada yang salah, tolong coba lagi',
                    'code' => 500,
                    'status' => 'STATUS_INTERNAL_SERVER_ERROR'
                ]
            ]
        ],
        status: HttpResponse::HTTP_INTERNAL_SERVER_ERROR,
        description: 'Internal Server Error'
    )]
    public function trashAll(PaginationRequest $request) : JsonResponse
    {
        try {
            $this->logger->info('receive request for get all trashed users by pagination');

            $userCollection = $this->userService->getAllTrashed($request);
            return $this->successResponse([
                'title' => 'Berhasil mendapatkan semua user yang terhapus',
                'status' => 'STATUS_OK',
                'code' => 200,
                'data' => $userCollection->collection,
                'meta' => [
                    'current_page' => $userCollection->currentPage(),
                    'last_page' => $userCollection->lastPage(),
                    'per_page' => $userCollection->perPage(),
                    'total' => $userCollection->total(),
                ]
            ]);
        } catch (\Exception $exception) {
            if ( $exception instanceof HttpResponseException ) {
                throw $exception;
            }
            $this->logger->error('failed processing request for get all trashed users by pagination',  [
                'error' => $exception->getMessage()
            ]);
            throw new HttpResponseException($this->errorInternalToResponse($exception, 'Gagal mendapatkan semua user yang terhapus'));
        }

    }

    #[Endpoint('Show Deleted / Trashed User', <<<DESC
  This endpoint allows you to show a user.
  It's a really useful endpoint, because this endpoint show a user has been deleted
 DESC)]
    #[Authenticated(authenticated: true)]
    #[Response(
        content: [
            'title' => 'Berhasil mendapatkan user yang terhapus',
            'status' => 'STATUS_OK',
            'code' => 200,
            'meta' => null,
            'data' => [
                'id' => '01JTT6XDDMS8WE4WM7...',
                'name' => 'johndoe',
                'email' => 'johndoe@gmail.com',
                'role' => 'student',
                'profile' => [
                    'gender' => 'male',
                    'about' => 'lorem ipsum',
                    'photo' => 'http://bailey.com/'
                ],
                'createdAt' => '2025-05-09T09:44:36.000000Z',
                'updatedAt' => '2025-05-09T09:44:36.000000Z'
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
                    'title' => 'Gagal mendapatkan user yang terhapus',
                    'details' => 'Sesuatu ada  yang salah, tolong coba lagi',
                    'code' => 500,
                    'status' => 'STATUS_INTERNAL_SERVER_ERROR'
                ]
            ]
        ],
        status: HttpResponse::HTTP_INTERNAL_SERVER_ERROR,
        description: 'Internal Server Error'
    )]
    public function showTrash(string $id): JsonResponse
    {
        try {
            $this->logger->info('receive request for show trashed user');

            $user = $this->userService->showTrash($id);

            return $this->successResponse([
                'title' => 'Berhasil mendapatkan user yang terhapus',
                'code' => HttpResponse::HTTP_OK,
                'status' => 'STATUS_OK',
                'data' => $user,
            ]);
        } catch (\Exception $exception) {
            if ( $exception instanceof HttpResponseException ) {
                throw $exception;
            }
            $this->logger->error('failed request for show trash user',  [
                'errors' => $exception->getMessage()
            ]);
            throw new HttpResponseException($this->errorInternalToResponse($exception, 'Gagal mendapatkan user yang terhapus'));
        }
    }

}
