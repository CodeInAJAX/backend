<?php

use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

$this->router->name('api.users.')->prefix('/v1/users')->controller(UserController::class)->group(function () {
    $this->router->name('create')->post('/', 'store');
});
