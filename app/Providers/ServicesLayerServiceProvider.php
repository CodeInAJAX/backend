<?php

namespace App\Providers;

use App\Http\Controllers\UserController;
use App\Models\Course;
use App\Models\User;
use App\Service\Contracts\CourseService;
use App\Service\Contracts\UserService;
use App\Service\Implements\CourseServiceImpl;
use App\Service\Implements\UserServiceImpl;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Log\Logger;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\Auth\Access\Gate;

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

        $this->app->bind(Course::class, function ($app) {
            return new Course();
        });

        $this->app->bind(
            UserService::class,
            function ($app) {
                return new UserServiceImpl(
                    $app->make(User::class),
                    $app->make(Logger::class),
                    $app->make(AuthFactory::class),
                    $app->make(Gate::class)
                );
            }
        );

        $this->app->bind(CourseService::class, function ($app) {
            return new CourseServiceImpl(
                $app->make(Course::class),
                $app->make(Logger::class),
                $app->make(AuthFactory::class),
                $app->make(Gate::class)
            );
        });

        $this->app->bind(UserController::class, function ($app) {
            return new UserController(
                $app->make(UserService::class),
                $app->make(Logger::class),
            );
        });

    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }

    public function provides(): array {
        return [UserService::class, UserController::class, CourseService::class];
    }
}
