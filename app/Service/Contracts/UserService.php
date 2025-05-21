<?php

namespace App\Service\Contracts;

use App\Http\Requests\FilterUserByEmailRequest;
use App\Http\Requests\LoginUserRequest;
use App\Http\Requests\PaginationUserRequest;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use \Symfony\Component\HttpFoundation\Cookie;


interface UserService
{
    public function getAll(PaginationUserRequest $data) :AnonymousResourceCollection;

    public function create(StoreUserRequest $data) : UserResource;

    public function login(LoginUserRequest $data) : array;

    public function setCookieWithRefreshToken(string $refreshToken) :Cookie;
   public function refresh(Request $request) :array;

    public function getByEmail(FilterUserByEmailRequest $data) : UserResource;

    public function update(UpdateUserRequest $data) : UserResource;
    public function delete(string $id) : array;

    public function restore(string $id): array;

}
