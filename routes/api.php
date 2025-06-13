<?php

use App\Http\Controllers\CourseController;
use App\Http\Controllers\UserController;


$this->router->name('api.users.')->prefix('/v1/users')->controller(UserController::class)->group(function () {
    $this->router->name('index')->get('/', 'index');
    $this->router->name('trash')->get('/trash', 'trashAll');
    $this->router->name('showTrash')->post('/trash/{id}', 'showTrash');
    $this->router->name('refresh')->get('/refresh-token', 'refresh');
    $this->router->name('create')->post('/', 'store');
    $this->router->name('login')->post('/login', 'login');
    $this->router->name('logout')->delete('/logout', 'logout');
    $this->router->name('show')->get('/{email}', 'show');
    $this->router->name('update')->patch('/', 'update');
    $this->router->name('destroy')->delete('/{id}', 'destroy');
    $this->router->name('restore')->post('/restore/{id}', 'restore');
});

$this->router->name('api.courses.')->prefix('/v1/courses')->controller(CourseController::class)->group(function () {
    $this->router->name('index')->get('/', 'index');
    $this->router->name('create')->post('/', 'store');
    $this->router->name('show')->get('/{id}', 'show');
    $this->router->name('update')->patch('/{id}', 'update');
    $this->router->name('destroy')->delete('/{id}', 'destroy');
});
