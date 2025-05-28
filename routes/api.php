<?php

use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

$this->router->name('api.users.')->prefix('/v1/users')->controller(UserController::class)->group(function () {
    $this->router->name('index')->get('/', 'index');
    $this->router->name('refresh')->get('/refresh-token', 'refresh');
    $this->router->name('create')->post('/', 'store');
    $this->router->name('login')->post('/login', 'login');
    $this->router->name('logout')->delete('/logout', 'logout');
    $this->router->name('show')->get('/{email}', 'show');
    $this->router->name('update')->patch('/', 'update');
    $this->router->name('destroy')->delete('/{id}', 'destroy');
    $this->router->name('restore')->post('/restore/{id}', 'restore');
});
