<?php

namespace App\Providers;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\User;
use App\Policies\CoursePolicy;
use App\Policies\EnrollmentPolicy;
use App\Policies\LessonPolicy;
use App\Policies\PaymentPolicy;
use App\Policies\UserPolicy;
use Illuminate\Database\Eloquent\Model;
//use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\Middleware\Authenticate;
use Symfony\Component\HttpFoundation\Response;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::automaticallyEagerLoadRelationships();
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Course::class, CoursePolicy::class);
        Gate::policy(Lesson::class, LessonPolicy::class);
        Gate::policy(Enrollment::class, EnrollmentPolicy::class);
        Gate::define('viewAllByCourse', [LessonPolicy::class, 'viewAllByCourse']);
        Gate::define('viewAll', [PaymentPolicy::class, 'viewAll']);
        Gate::define('viewAll', [EnrollmentPolicy::class, 'viewAll']);
        Authenticate::redirectUsing(function ($request) {

            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'errors' => [
                        'title' => 'User tidak terautentikasi',
                        'details' => 'Kamu harus terautentikasi untuk melakukan aksi ini',
                        'code' => Response::HTTP_UNAUTHORIZED,
                        'status' => 'STATUS_UNAUTHORIZED',
                    ]
                ], Response::HTTP_UNAUTHORIZED);
            }

            return redirect()->guest(route('login'));
        });
    }
}
