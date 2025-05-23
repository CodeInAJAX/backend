<?php

namespace App\Service\Contracts;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;

interface UserService
{
    public function getAll() :UserResource;

    public function create(StoreUserRequest $data) : UserResource;
    public function update(string $id, UpdateUserRequest $data) : UserResource;
    public function delete(string $id) : bool;

}
