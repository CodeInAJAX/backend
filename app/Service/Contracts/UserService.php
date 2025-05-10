<?php

namespace App\Service\Contracts;

use App\Http\Requests\LoginUserRequest;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use Illuminate\Cookie\Cookie;


interface UserService
{
    public function getAll() :UserResource;

    public function create(StoreUserRequest $data) : UserResource;
    public function login(LoginUserRequest $data) : array;

    public function setCookieWithRefreshToken(string $refreshToken) :Cookie;

    public function update(string $id, UpdateUserRequest $data) : UserResource;
    public function delete(string $id) : bool;

}
