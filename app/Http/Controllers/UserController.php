<?php

namespace App\Http\Controllers;

use App\Http\Requests\FilterUserByEmailRequest;
use App\Http\Requests\LoginUserRequest;
use App\Http\Requests\PaginationUserRequest;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use Illuminate\Auth\AuthenticationException;
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
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

#[Group("Users management", "APIs for managing user accounts")]
class UserController extends Controller implements HasMiddleware
{
    use HttpResponses;
    public function __construct(
        private UserService $userService,
        protected Logger $logger,
    )
    {
        $this->userService = $userService;
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
            'title' => 'Successfully Retrieved List Users By Pagination',
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
                    'title' => 'Users Unauthorized',
                    'details' => 'You must authenticate to perform this action.',
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
                    'title' => 'User Get All Failed',
                    'details' => 'Something went wrong. Please try again.',
                    'code' => 500,
                    'status' => 'STATUS_INTERNAL_SERVER_ERROR'
                ]
            ]
        ],
        status: HttpResponse::HTTP_INTERNAL_SERVER_ERROR,
        description: 'Internal Server Error'
    )]
    public function index(PaginationUserRequest $request) : JsonResponse
    {
       try {
           $this->logger->info('receive request for get all users by pagination');

           $userCollection = $this->userService->getAll($request);
            return $this->successResponse([
               'title' => 'Successfully Retrieved List Users By Pagination',
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

           $this->logger->error('failed processing request for get all users by pagination',  [
               'error' => $exception->getMessage()
           ]);
           throw new HttpResponseException($this->errorInternalToResponse($exception, 'User Get All Failed'));
       }
    }

    #[Endpoint('Create New Users', <<<DESC
  This endpoint allows you to register a new user.
  It's a really useful endpoint, and you should play around
  with it for a bit.
 DESC)]
    #[Response(
        content: [
            'title' => 'Successfully Registered Users',
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
                    'title' => 'Users Request Validation Failed',
                    'details' => [
                        'The email has already been taken.'
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
                    'title' => 'User Registration Failed',
                    'details' => 'Something went wrong. Please try again.',
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
                'title' => 'Successfully Registered Users',
                'code' => HttpResponse::HTTP_CREATED,
                'status' => 'STATUS_CREATED',
                'data' => $userRegistered,
            ]);
        } catch (\Exception $exception) {

            $this->logger->error('failed processing request for register users or create new user',  [
                'error' => $exception->getMessage()
            ]);

            throw new HttpResponseException($this->errorInternalToResponse($exception, 'User Registration Failed'));
        }
    }

    #[Endpoint('Login Existing User', <<<DESC
  This endpoint allows you to login a user.
  It's a really useful endpoint, because this endpoint result access token to access protected route
 DESC)]
    #[Response(
        content: [
            [
                'title' => 'Successfully logged in User',
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
                    'title' => 'Users Request Validation Failed',
                    'details' => [
                        'The confirm password field is required.'
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
                    'title' => 'User Login Failed',
                    'details' => 'Something went wrong. Please try again.',
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
                'title' => 'Successfully logged in User',
                'status' => 'STATUS_OK',
                'code' =>  200,
                'data' => $dataToken
            ])->withCookie($cookie);
        } catch (\Exception $exception) {
            $this->logger->error('failed request for login users to get access token', [
                'error' => $exception->getMessage()
            ]);

            throw new HttpResponseException($this->errorInternalToResponse($exception, 'User Login Failed'));
        }
    }

    #[Endpoint('Refresh Token', <<<DESC
  This endpoint allows you to refresh access token to access protected route.
  It's a really useful endpoint, because this endpoint result access token to access protected route
 DESC)]
    #[Response(
        content: [
            [
                'title' => 'Successfully Refreshed Token',
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
                    'title' => 'User Refresh Token Failed',
                    'details' => 'refresh token value not provided in cookie',
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
                    'title' => 'User Refresh Token Failed',
                    'details' => 'invalid credentials refresh token value',
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
                    'title' => 'User Refresh Token Failed',
                    'details' => 'Your refresh token must be no expired to perform this action. You can update the refresh token by login again',
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
                    'title' => 'User Refresh Token Failed',
                    'details' => 'Something went wrong. Please try again.',
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
                'title' => 'Successfully Refreshed token',
                'status' => 'STATUS_OK',
                'code' =>  200,
                'data' => $dataToken
            ]);
        } catch (\Exception $exception) {
            $this->logger->error('failed request for refresh token to get access token', [
                'error' => $exception->getMessage()
            ]);

            throw new HttpResponseException($this->errorInternalToResponse($exception, 'User Refresh Token Failed'));
        }
    }

    #[Endpoint('Logout Existing User', <<<DESC
  This endpoint allows you to logout a user.
  It's a really useful endpoint, because this endpoint logout account user
 DESC)]
    #[Authenticated(authenticated: true)]
    #[Response(
        content:  [
            "title" => "Successfully logged out User",
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
                    'title' => 'User Logout Failed',
                    'details' => 'Your token must be refresh to perform this action.',
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
                    'title' => 'User Logout Failed',
                    'details' => 'You must authenticate to perform this action.',
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
                    'title' => 'User Logout Failed',
                    'details' => 'Something went wrong. Please try again.',
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
                'title' => 'Successfully logged out User',
                'status' => 'STATUS_OK',
                'code' =>  200,
                'data' => null
            ])->withCookie($cookie);

        } catch (JWTException | TokenInvalidException | AuthenticationException $exception) {
            $this->logger->error('failed request for logout users to remove refresh token', [
                'errors' => $exception->getMessage()
            ]);
            throw new HttpResponseException($this->errorResponse([
                [
                    'title' => 'User Logout Failed',
                    'details' => 'You must authenticate to perform this action.',
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
                    'title' => 'User Logout Failed',
                    'details' => 'Your token must be refresh to perform this action.',
                    'code' => HttpResponse::HTTP_FORBIDDEN,
                    'status' => 'STATUS_FORBIDDEN',
                ]
            ]));
        } catch (\Exception $exception) {
            $this->logger->error('failed request for logout users to remove refresh token', [
                'error' => $exception->getMessage()
            ]);
            throw new HttpResponseException($this->errorInternalToResponse($exception, 'User Logout Failed'));
        }
    }

    #[Endpoint('Get a User By Email', <<<DESC
  This endpoint allows you to get user by email.
  It's a really useful endpoint, because this endpoint can see other user by email
 DESC)]
    #[Authenticated(authenticated: true)]
    #[Response(
        content: [
            'title' => 'Successfully Getting a User',
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
                    'title' => 'Users Unauthorized',
                    'details' => 'You must authenticate to perform this action.',
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
                    'title' => 'User Get By Email Failed',
                    'details' => 'Something went wrong. Please try again.',
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
                'title' => 'Successfully Getting a User',
                'code' => HttpResponse::HTTP_OK,
                'status' => 'STATUS_OK',
                'data' => $user,
            ]);

        } catch (\Exception $exception) {
            $this->logger->error('failed request for show users to see a user',  [
                'error' => $exception->getMessage()
            ]);
            throw new HttpResponseException($this->errorInternalToResponse($exception, 'User Get By Email Failed'));
        }
    }

    public function home()
    {

    }

    #[Endpoint('Update a User By Authentication', <<<DESC
  This endpoint allows you to update a user by authentication.
  It's a really useful endpoint, because this endpoint can update data user
 DESC)]
    #[Authenticated(authenticated: true)]
    #[Response(
        content: [
            'title' => 'Successfully Updated a User',
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
                    'title' => 'Users Unauthorized',
                    'details' => 'You must authenticate to perform this action.',
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
                    'title' => 'User Update Failed',
                    'details' => 'Something went wrong. Please try again.',
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
               'title' => 'Successfully Updated a User',
               'code' => HttpResponse::HTTP_OK,
               'status' => 'STATUS_OK',
                'data' => $user,
            ]);
        } catch (\Exception $exception) {
            $this->logger->error('failed request for update user',  [
               'errors' => $exception->getMessage()
            ]);
            throw new HttpResponseException($this->errorInternalToResponse($exception, 'User Update Failed'));
        }
    }

    #[Endpoint('Delete Existing User', <<<DESC
  This endpoint allows you to delete a user.
  It's a really useful endpoint, because this endpoint soft delete a user (ADMIN) Only
 DESC)]
    #[Authenticated(authenticated: true)]
    #[Response(
        content:  [
            "title" => "Successfully Delete a User",
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
                    'title' => 'Users Unauthorized',
                    'details' => 'You must authenticate to perform this action.',
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
                    'title' => 'User Delete Failed',
                    'details' => 'Something went wrong. Please try again.',
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
                'title' => 'Successfully Delete a User',
                'code' => HttpResponse::HTTP_OK,
                'status' => 'STATUS_OK',
                'data' => null,
                'meta' => $meta,
            ]);
        } catch (\Exception $exception) {
            $this->logger->error('failed request for delete user',  [
                'errors' => $exception->getMessage()
            ]);
            throw new HttpResponseException($this->errorInternalToResponse($exception, 'User Delete Failed'));
        }
    }

    #[Endpoint('Restore Deleted User', <<<DESC
  This endpoint allows you to restore a user.
  It's a really useful endpoint, because this endpoint restore a user has been deleted
 DESC)]
    #[Authenticated(authenticated: true)]
    #[Response(
        content:  [
            "title" => "Successfully Restore a User",
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
                    'title' => 'Users Unauthorized',
                    'details' => 'You must authenticate to perform this action.',
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
                    'title' => 'User Restore Failed',
                    'details' => 'Something went wrong. Please try again.',
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
                'title' => 'Successfully Restore a User',
                'code' => HttpResponse::HTTP_OK,
                'status' => 'STATUS_OK',
                'data' => null,
                'meta' => $meta,
            ]);
        } catch (\Exception $exception) {
            $this->logger->error('failed request for restore user',  [
                'errors' => $exception->getMessage()
            ]);
            throw new HttpResponseException($this->errorInternalToResponse($exception, 'User Restore Failed'));
        }
    }
}
