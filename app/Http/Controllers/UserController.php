<?php

namespace App\Http\Controllers;

use App\Http\Requests\FilterUserByEmailRequest;
use App\Http\Requests\LoginUserRequest;
use App\Http\Requests\StoreUserRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Log\Logger;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use App\Service\Contracts\UserService;
use App\Traits\HttpResponses;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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


    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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

            $this->logger->error('failed processing request for register users or create new user');

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
            $this->logger->error('failed request for login users to get access token');

            throw new HttpResponseException($this->errorInternalToResponse($exception, 'User Login Failed'));
        }
    }

    #[Endpoint('Logout Existing User', <<<DESC
  This endpoint allows you to logout a user.
  It's a really useful endpoint, because this endpoint logout account user
 DESC)]
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

        } catch (\Exception $exception) {
            $this->logger->error('failed request for logout users to remove refresh token');
            throw new HttpResponseException($this->errorInternalToResponse($exception, 'User Logout Failed'));
        }
    }

    #[Endpoint('Get a User By Email', <<<DESC
  This endpoint allows you to get user by email.
  It's a really useful endpoint, because this endpoint can see other user by email
 DESC)]
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
            $this->logger->error('failed request for show users to see a user');
            throw new HttpResponseException($this->errorInternalToResponse($exception, 'User Filter Failed'));
        }
    }

    public function home()
    {

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

}
