<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginUserRequest;
use App\Http\Requests\StoreUserRequest;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use App\Service\Contracts\UserService;
use App\Traits\HttpResponses;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

#[Group("Users management", "APIs for managing user accounts")]
class UserController extends Controller
{
    use HttpResponses;
    public function __construct(
        private UserService $userService
    )
    {
        $this->userService = $userService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    #[Endpoint("Create New Users", <<<DESC
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
                    'status' => 'Bad Request'
                ]
            ]
        ],
        status: HttpResponse::HTTP_BAD_REQUEST,
        description: 'Bad Request'
    )]
    public function store(StoreUserRequest $request): JsonResponse
    {
        $userRegistered = $this->userService->create($request);
        return $this->successResponse([
            'title' => 'Successfully Registered Users',
            'code' => HttpResponse::HTTP_CREATED,
            'status' => 'STATUS_CREATED',
            'data' => $userRegistered,
        ]);
    }

    public function login(LoginUserRequest $request): JsonResponse
    {
        $dataToken = $this->userService->login($request);
        $this->userService->setCookieWithRefreshToken($dataToken['refresh_token']);
        unset($dataToken['refresh_token']);
        return $this->successResponse([
            'title' => 'Successfully logged in User',
            'status' => 'STATUS_OK',
            'code' =>  200,
            'data' => $dataToken
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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
