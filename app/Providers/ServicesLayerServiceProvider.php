<?php

namespace App\Providers;

use App\Models\User;
use App\Service\Contracts\UserService;
use App\Service\Implements\UserServiceImpl;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Log\Logger;
use Illuminate\Support\ServiceProvider;


class ServicesLayerServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(User::class, function ($app) {
            return new User();
        });
        $this->app->bind(
            UserService::class,
            function ($app) {
                $userModel = $app->make(User::class);
                $logger = $app->make(Logger::class);
                return new UserServiceImpl(
                    $userModel,
                    $logger
                );
            }
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }

    public function provides(): array {
        return [UserService::class];
    }
}
