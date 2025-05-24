<?php

namespace App\Service\Implements;

use App\Enums\Gender;
use App\Enums\Role;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Service\Contracts\UserService;
use App\Traits\HttpResponses;
use Illuminate\Database\Eloquent\MissingAttributeException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Log\Logger;
use Illuminate\Support\Facades\Hash;

class UserServiceImpl implements UserService
{

    use HttpResponses;
    /**
     * Create a new class instance.
     */
    public function __construct(
        protected User $user,
        protected Logger $logger,
    )
    {
    }

    public function getAll(): UserResource
    {
        return new UserResource();
    }

    public function create(StoreUserRequest $data): UserResource
    {
        try {
            $this->logger->info('starts the process of creating a user or registering a user');
            // Validate the request
            $validated = $data->validated();

            $this->logger->info('successfully passed the validation process, then the process of creating a user');
            // Create the user
            $user = $this->user->newQuery()->create([
                'name'     => $validated['name'],
                'email'    => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role'     =>  Role::from($validated['role']),
            ]);

            $this->logger->info('successfully pass the process of creating a user, then create a user profile');

            $user->profile()->create([
                'gender' => Gender::from($validated['gender']),
                'photo' => $validated['photo'],
                'about' => $validated['about'] ?? 'No description',
            ]);

            $user->refresh();
            $user->profile()->get();
            $this->logger->info('successfully goes through all the subsequent processes returning the result');

            return new UserResource($user);

        } catch (MissingAttributeException $exception) {
            throw new HttpResponseException(
                $this->errorResponse([
                    [
                        'title' => 'Missing Attribute Users Request',
                        'details' => $exception->getMessage(),
                        'code' => 400,
                        'status' => 'Bad Request'
                    ]
                ])
            );
        }
    }

    public function update(string $id, UpdateUserRequest $data): UserResource
    {
        return new UserResource();
    }

    public function delete(string $id): bool
    {
        return true;
    }
}
