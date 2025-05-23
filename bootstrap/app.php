<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (JWTException | TokenInvalidException | TokenExpiredException | AuthenticationException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                if ($e instanceof TokenExpiredException) {
                    return response()->json([
                        'errors' => [
                            'title' => 'Users Forbidden',
                            'detail' => 'Your token must be refresh to perform this action.',
                            'code' => Response::HTTP_FORBIDDEN,
                            'status' => 'STATUS_FORBIDDEN',
                        ]
                    ], Response::HTTP_UNAUTHORIZED);
                }
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

    })->create();
