<?php

namespace App\Providers;

use App\Http\Controllers\UserController;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\Payment;
use App\Models\User;
use App\Service\Contracts\CourseService;
use App\Service\Contracts\EnrollmentService;
use App\Service\Contracts\LessonService;
use App\Service\Contracts\PaymentService;
use App\Service\Contracts\UserService;
use App\Service\Implements\CourseServiceImpl;
use App\Service\Implements\EnrollmentServiceImpl;
use App\Service\Implements\LessonServiceImpl;
use App\Service\Implements\PaymentServiceImpl;
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

        $this->app->bind(Lesson::class, function ($app) {
            return new Lesson();
        });

        $this->app->bind(Payment::class, function ($app) {
           return new Payment();
        });

        $this->app->bind(Enrollment::class, function ($app) {
            return new Enrollment();
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

        $this->app->bind(LessonService::class, function ($app) {
            return new LessonServiceImpl(
                $app->make(Lesson::class),
                $app->make(Course::class),
                $app->make(Logger::class),
                $app->make(AuthFactory::class),
                $app->make(Gate::class)
            );
        });

        $this->app->bind(PaymentService::class, function ($app) {
            return new PaymentServiceImpl(
                $app->make(Payment::class),
                $app->make(Course::class),
                $app->make(Enrollment::class),
                $app->make(Logger::class),
                $app->make(AuthFactory::class),
                $app->make(Gate::class)
            );
        });

        $this->app->bind(EnrollmentService::class, function ($app) {
            return new EnrollmentServiceImpl(
                $app->make(Payment::class),
                $app->make(Course::class),
                $app->make(Enrollment::class),
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
        return [UserService::class, UserController::class, CourseService::class,  LessonService::class, PaymentService::class, EnrollmentService::class];
    }
}
