<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
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
        Authenticate::redirectUsing(function ($request) {

            if ($request->expectsJson()) {
                return response()->json([
                    'errors' => [
                        'title' => 'Users Unauthorized',
                        'detail' => 'You must authenticate to perform this action.',
                        'code' => Response::HTTP_UNAUTHORIZED,
                        'status' => 'STATUS_UNAUTHORIZED',
                    ]
                ], Response::HTTP_UNAUTHORIZED);
            }

            if ($request->is('api/*')) {
                return response()->json([
                    'errors' => [
                        'title' => 'Users Unauthorized',
                        'detail' => 'You must authenticate to perform this action.',
                        'code' => Response::HTTP_UNAUTHORIZED,
                        'status' => 'STATUS_UNAUTHORIZED',
                    ]
                ], Response::HTTP_UNAUTHORIZED);
            }

            return redirect()->guest(route('login'));
        });
    }
}
