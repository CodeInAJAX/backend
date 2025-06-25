<?php

namespace App\Service\Implements;

use App\Enums\Gender;
use App\Enums\Role;
use App\Http\Requests\FilterUserByEmailRequest;
use App\Http\Requests\LoginUserRequest;
use App\Http\Requests\PaginationRequest;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Service\Contracts\UserService;
use App\Traits\HttpResponses;
//use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Carbon;
//use Illuminate\Validation\ValidationException;
use \Symfony\Component\HttpFoundation\Cookie;
use Illuminate\Database\Eloquent\MissingAttributeException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Log\Logger;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Tymon\JWTAuth\JWTGuard;
use Illuminate\Contracts\Auth\Access\Gate;

class UserServiceImpl implements UserService
{

    use HttpResponses;
    protected StatefulGuard|Guard|JWTGuard $authGuard;
    /**
     * Create a new class instance.
     */
    public function __construct(
        protected User $user,
        protected Logger $logger,
        AuthFactory $authFactory,
        protected Gate $gate,
    )
    {
        $this->authGuard = $authFactory->guard('api');
    }

    public function getAll(PaginationRequest $data): AnonymousResourceCollection
    {
        try {
            $this->logger->info('starts the process of listing users by pagination');
            // Validate the request
            $validate = $data->validated();
            $page = $validate['page'] ?? 1;
            $size = $validate['size'] ?? 10;

            $this->logger->info('successfully passed the validation process, then the process of listing a user');
            $this->logger->info("pagination request received: page={$page}, size={$size}");

            $query = $this->user->newQuery()->with('profile');

            $users = $query->paginate(perPage: $size, page: $page);


            $this->logger->info('successfully retrieved paginated users');
            $this->logger->info('successfully goes through all the subsequent processes returning the result');
            return UserResource::collection($users);
        } catch (\Exception $exception){
            if ( $exception instanceof HttpResponseException ) {
                throw $exception;
            }
            $this->logger->error('failed processing request for listing users',  [
                'error' => $exception->getMessage()
            ]);

            throw new HttpResponseException($this->errorInternalToResponse($exception, 'Gagal mendapatkan semua user berdasarkan penomoran halaman'));
        }
    }


    public function getAllTrashed(PaginationRequest $data): AnonymousResourceCollection
    {
        try {
            $this->logger->info('starts the process of authentication and authorization');
            $user = $this->authGuard->user();
            if (!$user) {
                $this->logger->error('failed processing request for listing trash user: User not authenticated');
                throw new HttpResponseException($this->errorResponse([
                    [
                        'title' => 'Gagal mendapatkan semua user yang sudah di hapus',
                        'details' => 'gagal mendapatkan daftar semua user yang sudah di hapus karena user tidak terautentikasi',
                        'code' => HttpResponse::HTTP_UNAUTHORIZED,
                        'status' => 'STATUS_UNAUTHORIZED'
                    ]
                ]));
            }
            if (!$this->gate->allows('viewAny', User::class)) {
                $this->logger->warning('Get All Trash user unauthorized: Admin role required', [
                    'user_id' => $user->id,
                    'user_role' => $user->role
                ]);
                throw new HttpResponseException($this->errorResponse([
                    [
                        'title' => 'Gagal mendapatkan semua user yang sudah di hapus',
                        'details' => 'gagal mendapatkan daftar semua user yang sudah di hapus karena user tidak diizinkan melakukan aksi ini',
                        'code' => HttpResponse::HTTP_FORBIDDEN,
                        'status' => 'STATUS_FORBIDDEN'
                    ]
                ]));
            }
            $this->logger->info('starts the process of listing trash users by pagination');
            // Validate the request
            $validate = $data->validated();
            $page = $validate['page'] ?? 1;
            $size = $validate['size'] ?? 10;

            $this->logger->info('successfully passed the validation process, then the process of listing trash a user');
            $this->logger->info("pagination request received: page={$page}, size={$size}");

            $query = $this->user->withTrashed()->onlyTrashed()->with('profile');

            $users = $query->paginate(perPage: $size, page: $page);


            $this->logger->info('successfully retrieved paginated users');
            $this->logger->info('successfully goes through all the subsequent processes returning the result');
            return UserResource::collection($users);
        } catch (\Exception $exception){
            if ( $exception instanceof HttpResponseException ) {
                throw $exception;
            }
            $this->logger->error('failed processing request for listing trashed users',  [
                'error' => $exception->getMessage()
            ]);

            throw new HttpResponseException($this->errorInternalToResponse($exception, 'Gagal mendapatkan semua user yang sudah di hapus'));
        }
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
            throw new HttpResponseException($this->errorResponse([
                [
                    'title' => 'Gagal membuat user',
                    'details' => 'atribut yang diperlukan hilang untuk detail nya di meta properti',
                    'code' => HttpResponse::HTTP_BAD_REQUEST,
                    'status' => 'STATUS_BAD_REQUEST',
                    'meta' => [
                        'en' => [
                            'error' => $exception->getMessage()
                        ]
                    ]
                ]
            ]));
        }
    }

    /**
     * Update the authenticated user with the provided data
     *
     * @param UpdateUserRequest $data
     * @return UserResource
     */
    public function update(UpdateUserRequest $data): UserResource
    {
        try {
            $this->logger->info('starting the process of updating a user by authentication');
            // Get the user from auth
            $user = $this->authGuard->user();
            if (!$user) {
                $this->logger->error('failed processing request for update user: User not authenticated');
                throw new HttpResponseException($this->errorResponse([
                    [
                        'title' => 'Gagal memperbarui user',
                        'details' => 'gagal memperbarui user karena user tidak terautentikasi',
                        'code' => HttpResponse::HTTP_UNAUTHORIZED,
                        'status' => 'STATUS_UNAUTHORIZED'
                    ]
                ]));
            }

            // Check if user is authorized to update
            if (!$this->gate->allows('update', $user)) {
                $this->logger->error('failed processing request for update user: Not authorized', [
                    'user_id' => $user->id
                ]);
                throw new HttpResponseException($this->errorResponse([
                    [
                        'title' => 'Gagal memperbarui user',
                        'details' => 'gagal memperbarui user karena user tidak diizinkan melakukan aksi ini',
                        'code' => HttpResponse::HTTP_FORBIDDEN,
                        'status' => 'STATUS_FORBIDDEN'
                    ]
                ]));
            }

            $validated = $data->validated();
            $this->logger->info('successfully passed validation process', [
                'user_id' => $user->id,
                'fields' => array_keys($validated)
            ]);

            $this->logger->info('successfully passed authorization, proceeding with update');
            $updatedFields = [];

            // Handle email update separately to check for uniqueness
            if (isset($validated['email']) && $validated['email'] !== $user->email) {
                $existingUser = User::where('email', $validated['email'])->where('id', '!=', $user->id)->exists();
                if ($existingUser) {
                    $this->logger->error('update user failed: Email already in use', [
                        'user_id' => $user->id,
                        'email' => $validated['email']
                    ]);
                    throw new HttpResponseException($this->errorResponse([
                        [
                            'title' => 'Gagal memperbarui user',
                            'details' => 'gagal memperbarui user karena data email user yang diperbarui sudah digunakan',
                            'code' => HttpResponse::HTTP_CONFLICT,
                            'status' => 'STATUS_CONFLICT'
                        ]
                    ]));
                }
                $user->email = $validated['email'];
                $updatedFields[] = 'email';
            }

            // Handle basic user fields
            $userFields = ['name', 'role', 'password'];
            foreach ($userFields as $field) {
                if (isset($validated[$field])) {
                    if ($field === 'password') {
                        $user->password = Hash::make($validated[$field]);
                    } else {
                        $user->$field = $validated[$field];
                    }
                    $updatedFields[] = $field;
                }
            }

            // Save user changes
            $user->save();

            // Handle profile-related fields
            $profileFields = ['photo', 'gender', 'about'];
            $profileUpdated = false;

            // Get or create the user profile
            $profile = $user->profile;
            if (!$profile) {
                $profile = new Profile();
                $profile->user_id = $user->id;
            }

            foreach ($profileFields as $field) {
                if (isset($validated[$field])) {
                    $profile->$field = $validated[$field];
                    $updatedFields[] = $field;
                    $profileUpdated = true;
                }
            }

            // Save profile if any changes were made
            if ($profileUpdated) {
                $profile->save();
            }

            // Refresh user with profile relationship
            $user->refresh();
            $user->load('profile');

            $this->logger->info('User update successful', [
                'user_id' => $user->id,
                'updated_fields' => $updatedFields
            ]);

            return new UserResource($user);
        } catch (ModelNotFoundException $exception) {
            $this->logger->error('Failed processing update user: User not found', [
                'error' => $exception->getMessage()
            ]);
            $this->logger->error('failed processing update user', [
                'errors' => $exception->getMessage()
            ]);
            throw new HttpResponseException($this->errorResponse([
                [
                    'title' => 'Gagal memperbarui user',
                    'details' => 'gagal memperbarui user karena user tidak ditemukan',
                    'code' => HttpResponse::HTTP_NOT_FOUND,
                    'status' => 'STATUS_NOT_FOUND',
                ]
            ]));
        } catch (\Exception $exception) {
            if ( $exception instanceof HttpResponseException ) {
                throw $exception;
            }
            $this->logger->error('Failed processing update user: Unexpected error', [
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString()
            ]);
            throw new HttpResponseException($this->errorInternalToResponse($exception,'Gagal memperbarui user'));
        }
    }

    public function delete(string $id): array
    {
        $this->logger->info('starting the process of delete a user (admin)');
        // Get the authenticated user
        $user =  $this->authGuard->user();
        $this->logger->info('Delete user attempt', [
            'admin_id' => $user ? $user->id : 'unauthenticated',
            'target_user_id' => $id
        ]);

        // Check if user is authenticated
        if (!$user) {
            $this->logger->error('Delete user failed: User not authenticated');
            throw new HttpResponseException($this->errorResponse([
                [
                    'title' => 'Gagal menghapus user',
                    'details' => 'gagal menghapus user karena user tidak terautentikasi',
                    'code' => HttpResponse::HTTP_UNAUTHORIZED,
                    'status' => 'STATUS_UNAUTHORIZED'
                ]
            ]));
        }

        // Find the user to delete
        $userToDelete = $this->user->newQuery()->find($id);

        if (!$userToDelete) {
            $this->logger->error('Delete user failed: User not found', ['target_user_id' => $id]);
            throw new HttpResponseException(
                $this->errorResponse([
                    [
                        'title' => 'Gagal menghapus user',
                        'details' => 'gagal menghapus user karena user yang akan dihapus tidak ditemukan',
                        'code' => HttpResponse::HTTP_NOT_FOUND,
                        'status' => 'STATUS_NOT_FOUND'
                    ]
                ])
            );
        }

        // Check if user is authorized to delete
        if (!$this->gate->allows('delete', $userToDelete)) {
            $this->logger->warning('Delete user unauthorized: Admin role required', [
                'user_id' => $user->id,
                'user_role' => $user->role
            ]);
            throw new HttpResponseException($this->errorResponse([
                [
                    'title' => 'Gagal menghapus user',
                    'details' => 'gagal menghapus user karena user tidak diizinkan melakukan aksi ini',
                    'code' => HttpResponse::HTTP_FORBIDDEN,
                    'status' => 'STATUS_FORBIDDEN'
                ]
            ]));
        }

        // Soft delete the user
        $userToDelete->delete();

        $this->logger->info('User deleted successfully', [
            'admin_id' => $user->id,
            'deleted_user_id' => $userToDelete->id,
            'deleted_user_email' => $userToDelete->email
        ]);

        return [
            'admin_id' => $user->id,
            'deleted_user_id' => $userToDelete->id,
        ];
    }

    public function restore(string $id): array
    {
        $this->logger->info('starting the process of restore a user (admin)');
        // Get the authenticated user
        $user =  $this->authGuard->user();
        $this->logger->info('Restore user attempt', [
            'admin_id' => $user ? $user->id : 'unauthenticated',
            'target_user_id' => $id
        ]);

        // Check if user is authenticated
        if (!$user) {
            $this->logger->error('Restore user failed: User not authenticated');
            throw new HttpResponseException($this->errorResponse([
                [
                    'title' => 'Gagal mengambalikan user yang terhapus',
                    'details' => 'gagal mengambalikan user yang terhapus, karena user tidak terautentikasi',
                    'code' => HttpResponse::HTTP_UNAUTHORIZED,
                    'status' => 'STATUS_UNAUTHORIZED'
                ]
            ]));
        }

        // Find the user to restore
        $userToRestore = User::withTrashed()->find($id);

        if (!$userToRestore) {
            $this->logger->error('Restore user failed: User not found', ['target_user_id' => $id]);
            throw new HttpResponseException(
                $this->errorResponse([
                    [
                        'title' => 'Gagal mengembalikan user yang terhapus',
                        'details' => 'gagal mengembalikan user yang terhapus, karena user yang dikembalikan tidak ditemukan',
                        'code' => HttpResponse::HTTP_NOT_FOUND,
                        'status' => 'STATUS_NOT_FOUND'
                    ]
                ])
            );
        }

        // Check if user is authorized to restore
        if (!$this->gate->allows('restore', $userToRestore)) {
            $this->logger->warning('Restore user unauthorized: Admin role required', [
                'user_id' => $user->id,
                'user_role' => $user->role
            ]);
            throw new HttpResponseException($this->errorResponse([
                [
                    'title' => 'Gagal mengembalikan user terhapus',
                    'details' => 'gagal mengembalikan user terhapus karena user tidak diizinkan melakukan aksi ini',
                    'code' => HttpResponse::HTTP_FORBIDDEN,
                    'status' => 'STATUS_FORBIDDEN'
                ]
            ]));
        }

        // restore the user
        $userToRestore->restore();

        $this->logger->info('User restore successfully', [
            'admin_id' => $user->id,
            'restore_user_id' => $userToRestore->id,
            'restore_user_email' => $userToRestore->email
        ]);

        return [
            'admin_id' => $user->id,
            'restore_user_id' => $userToRestore->id,
        ];
    }


    public function login(LoginUserRequest $data): array
    {
        $this->logger->info('starts the process of logging in a user');

        $data->validated();

        $this->logger->info('successfully passed the validation process, then the process of authenticating user');

        $accessToken =  $this->authGuard->attempt($data->only('email', 'password'));

        if (!$accessToken) {
            throw new HttpResponseException($this->errorResponse([
                [
                    'title' => 'Gagal login user',
                    'details' => 'gagal login user karena tidak valid kredensial user, mungkin email atau password yang salah ',
                    'code' => 401,
                    'status' => 'STATUS_UNAUTHORIZED',
                    'meta' => [
                        'value' => [
                            'email' => $data->get('email'),
                            'password' => $data->get('password')
                        ]
                    ]
                ]
            ]));
        }

        $this->logger->info('successfully passed the process of authenticating user, then the process of create refresh token');

        $refreshToken = $this->createRefreshToken($data->only('email', 'password'));

        if (!$refreshToken) {
            throw new HttpResponseException($this->errorResponse([
                [
                    'title' => 'Gagal login user',
                    'details' => 'gagal login user karena tidak valid kredensial user, mungkin email atau password yang salah ',
                    'code' => 401,
                    'status' => 'STATUS_UNAUTHORIZED',
                    'meta' => [
                        'value' => [
                            'email' => $data->get('email'),
                            'password' => $data->get('password')
                        ]
                    ]
                ]
            ]));
        }

        $this->logger->info('successfully goes through all the subsequent processes returning the result');

        return [
            'access_token' => $accessToken,
            'token_type' => 'Bearer',
            'expires_in' =>  $this->authGuard->factory()->getTTL() * 60,
            'refresh_token' => $refreshToken
        ];
    }

    private function createRefreshToken($credential): string
    {
        // Force the refresh TTL to be an integer
        $refreshTokenTTL = (int) config('jwt.refresh_ttl', 1440);

        // Make sure we're passing an integer to Carbon and JWT
        return  $this->authGuard->claims([
            'refresh' => true,
            'exp' => Carbon::now()->addMinutes($refreshTokenTTL)->timestamp
        ])->setTTL($refreshTokenTTL)->attempt($credential);
    }

    public function setCookieWithRefreshToken(string $refreshToken) : Cookie
    {
        $this->logger->info('starts the process of setting cookie with valur refresh token');
        return cookie(
            'refresh_token',
            $refreshToken,
            config('jwt.refresh_ttl', 1440),
            '/',
            null,                          // Domain (null = current domain)
            config('app.env') !== 'local', // Secure (true di production)
            true,                          // HTTP Only
            false,                         // Raw
            'Lax'                       // SameSite policy
        );
    }

    public function getByEmail(FilterUserByEmailRequest $data): UserResource
    {
        $this->logger->info('starts the process of getting a user by email');
        $data->validated();

        $user = $this->user->newQuery()
            ->with('profile')
            ->where('email', $data->route('email'))
            ->first();

        if (!$user) {
            $this->logger->error('Get User By Email Failed: User not found', ['user_email' => $data->route('email')]);
            throw new HttpResponseException(
                $this->errorResponse([
                    [
                        'title' => 'Gagal menemukan user berdasarkan Email',
                        'details' => 'gagal menemukan user berdasarkan email, karena user yang dikembalikan tidak ditemukan',
                        'code' => HttpResponse::HTTP_NOT_FOUND,
                        'status' => 'STATUS_NOT_FOUND'
                    ]
                ])
            );
        }

        $this->logger->info('successfully goes through all the subsequent processes returning the result');

        return new UserResource($user);
    }


    public function show(): UserResource
    {
        $this->logger->info('starting the process of get detail user by id');
        // Get the user from auth
        $user = $this->authGuard->user();
        if (!$user) {
            $this->logger->error('failed processing request for get detail user by id: User not authenticated');
            throw new HttpResponseException($this->errorResponse([
                [
                    'title' => 'Gagal mendapatkan detail user',
                    'details' => 'gagal mendapatkan detail user karena user tidak terautentikasi',
                    'code' => HttpResponse::HTTP_UNAUTHORIZED,
                    'status' => 'STATUS_UNAUTHORIZED'
                ]
            ]));
        }

        // Check if user is authorized to view
        if (!$this->gate->allows('view', $user)) {
            $this->logger->error('failed processing request for update user: Not authorized', [
                'user_id' => $user->id
            ]);
            throw new HttpResponseException($this->errorResponse([
                [
                    'title' => 'Gagal memperbarui user',
                    'details' => 'gagal memperbarui user karena user tidak diizinkan melakukan aksi ini',
                    'code' => HttpResponse::HTTP_FORBIDDEN,
                    'status' => 'STATUS_FORBIDDEN'
                ]
            ]));
        }
        $this->logger->info('starts the process of getting a detail user');

        $user = $this->user->newQuery()
            ->with(['profile','courses.payments','enrolledCourses', 'payments'])
            ->find($user->id);

        if (!$user) {
            $this->logger->error('Get Detail User Failed: User not found', ['user_id' => $user->id ]);
            throw new HttpResponseException(
                $this->errorResponse([
                    [
                        'title' => 'Gagal menemukan detail user',
                        'details' => 'gagal menemukan detail user, karena user yang dikembalikan tidak ditemukan',
                        'code' => HttpResponse::HTTP_NOT_FOUND,
                        'status' => 'STATUS_NOT_FOUND'
                    ]
                ])
            );
        }

        $this->logger->info('successfully goes through all the subsequent processes returning the result');

        return new UserResource($user);
    }

    public function refresh(Request $request): array
    {
        try {

            $this->logger->info('starts the process of refresh token to generate access token');

            $refreshToken = $request->cookie('refresh_token');

            if (!$refreshToken) {
                $this->logger->error('failed processing refresh token because refresh token is missing');
                throw new HttpResponseException($this->errorResponse([
                    [
                        'title' => 'Gagal menyegarkan token',
                        'details' => "gagal menyegarkan token, karena nilai 'refresh_token' tidak tersedia di cookie",
                        'code' => 400,
                        'status' => 'STATUS_BAD_REQUEST',
                    ]
                ]));
            }

            $this->logger->info('successfully get the next token refresh payload validation process');

            $payload =  $this->authGuard->setToken($refreshToken)->payload();
            if (!$payload || !isset($payload['refresh']) || $payload['refresh'] !== true) {
                $this->logger->error('failed processing refresh token because invalid refresh token');
                throw new HttpResponseException($this->errorResponse([
                    [
                        'title' => 'Gagal menyegarkan token',
                        'details' => "gagal menyegarkan token karena tidak valid isi data 'refresh_token'",
                        'code' => 401,
                        'status' => 'STATUS_UNAUTHORIZED',
                    ]
                ]));
            }

            $this->logger->info('successful payload validation then find the users to recreate access tokens');

            $user = $this->user->newQuery()
                ->findOrFail($payload['sub']);

            $accessToken =  $this->authGuard->login($user);

            $this->logger->info('successfully recreate the access token next return the result');

            return [
                'access_token' => $accessToken,
                'token_type' => 'Bearer',
                'expires_in' => $this->authGuard->factory()->getTTL() * 60,
            ];
        } catch (ModelNotFoundException $exception) {
            $this->logger->error('failed processing refresh token for refresh access token', [
                'errors' => $exception->getMessage()
            ]);
            throw new HttpResponseException($this->errorResponse([
                [
                    'title' => 'Gagal menyegarkan token',
                    'details' => 'user yang akan disegarkan token tidak ditemukan, sebaiknya melakukan daftar akun dahulu',
                    'code' => HttpResponse::HTTP_NOT_FOUND,
                    'status' => 'STATUS_NOT_FOUND',
                ]
            ]));
        }  catch (TokenExpiredException $exception) {
            $this->logger->error('failed processing refresh token for refresh access token', [
                'errors' => $exception->getMessage()
            ]);
            throw new HttpResponseException($this->errorResponse([
                [
                    'title' => 'Gagal menyegarkan token',
                    'details' => "'refresh_token' sudah kadaluarsa, anda dapat mendapatkan nya kembali setelah melakukan login ulang",
                    'code' => HttpResponse::HTTP_FORBIDDEN,
                    'status' => 'STATUS_FORBIDDEN',
                ]
            ]));
        }
    }

    public function showTrash(string $id): UserResource
    {
        try {
            $this->logger->info('starts the process of authentication and authorization');
            $user = $this->authGuard->user();
            if (!$user) {
                $this->logger->error('failed processing request for listing trash user: User not authenticated');
                throw new HttpResponseException($this->errorResponse([
                    [
                        'title' => 'Mendapatkan user yang terhapus berdasarkan id',
                        'details' => 'gagal mendapatkan user yang terhapus karena user tidak ditemukan',
                        'code' => HttpResponse::HTTP_UNAUTHORIZED,
                        'status' => 'STATUS_UNAUTHORIZED'
                    ]
                ]));
            }
            if (!$this->gate->allows('viewAny', User::class)) {
                $this->logger->warning('Get User Trash By ID unauthorized: Admin role required', [
                    'user_id' => $user->id,
                    'user_role' => $user->role
                ]);
                throw new HttpResponseException($this->errorResponse([
                    [
                        'title' => 'Mendapatkan user yang terhapus berdasarkan id',
                        'details' => 'gagal mendapatkan user yang terhapus, karena user tidak diizinkan melakukan aksi ini',
                        'code' => HttpResponse::HTTP_FORBIDDEN,
                        'status' => 'STATUS_FORBIDDEN'
                    ]
                ]));
            }
            // Validate the request
            $user = $this->user->withTrashed()->onlyTrashed()->find($id);
            if (!$user) {
                $this->logger->error('failed processing request for get trash user: User not found');
                throw new HttpResponseException($this->errorResponse([
                    [
                        'title' => 'Mendapatkan user yang terhapus berdasarkan id',
                        'details' => 'gagal mendapatkan user yang terhapus karena user tidak ditemukan didaftar user terhapus',
                        'code' => HttpResponse::HTTP_NOT_FOUND,
                        'status' => 'STATUS_NOT_FOUND'
                    ]
                ]));
            }


            $this->logger->info('successfully retrieved get trash user by id');
            $this->logger->info('successfully goes through all the subsequent processes returning the result');
            return new UserResource($user);
        } catch (\Exception $exception){
            if ( $exception instanceof HttpResponseException ) {
                throw $exception;
            }
            $this->logger->error('failed processing request for get trashed users',  [
                'error' => $exception->getMessage()
            ]);

            throw new HttpResponseException($this->errorInternalToResponse($exception, 'Gagal mendapatkan user yang terhapus berdasarkan id'));
        }
    }

}
