<?php

use App\Http\Controllers\CourseController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\LessonCompletionController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\UserController;


$this->router->name('api.users.')->prefix('/v1/users')->controller(UserController::class)->group(function () {
    $this->router->name('index')->get('/', 'index');
    $this->router->name('trash')->get('/trash', 'trashAll');
    $this->router->name('showTrash')->post('/trash/{id}', 'showTrash');
    $this->router->name('refresh')->get('/refresh-token', 'refresh');
    $this->router->name('create')->post('/', 'store');
    $this->router->name('login')->post('/login', 'login');
    $this->router->name('logout')->delete('/logout', 'logout');
    $this->router->name('detail')->get('/detail', 'detail');
    $this->router->name('show')->get('/{email}', 'show');
    $this->router->name('update')->patch('/', 'update');
    $this->router->name('destroy')->delete('/{id}', 'destroy');
    $this->router->name('restore')->post('/restore/{id}', 'restore');
});

$this->router->name('api.courses.')->prefix('/v1/courses')->controller(CourseController::class)->group(function () {
    $this->router->name('index')->get('/', 'index');
    $this->router->name('search')->get('/search', 'search');
    $this->router->name('create')->post('/', 'store');
    $this->router->name('show')->get('/{id}', 'show');
    $this->router->name('detail')->get('/{id}/detail', 'detail');
    $this->router->name('update')->patch('/{id}', 'update');
    $this->router->name('destroy')->delete('/{id}', 'destroy');
});

$this->router->name('api.lessons.')->prefix('/v1/courses')->controller(LessonController::class)->group(function () {
    $this->router->name('index')->get('/{courseId}/lessons', 'index');
    $this->router->name('create')->post('/{courseId}/lessons', 'store');
    $this->router->name('show')->get('/{courseId}/lessons/{lessonId}', 'show');
    $this->router->name('update')->patch('/{courseId}/lessons/{lessonId}', 'update');
    $this->router->name('destroy')->delete('/{courseId}/lessons/{lessonId}', 'destroy');
});

$this->router->name('api.payments.')->prefix('/v1/payments')->controller(PaymentController::class)->group(function () {
    $this->router->name('index')->get('/', 'index');
    $this->router->name('create')->post('/', 'store');
    $this->router->name('show')->get('/{id}', 'show');
    $this->router->name('update')->patch('/{id}', 'update');
    $this->router->name('destroy')->delete('/{id}', 'destroy');
});

$this->router->name('api.enrollments')->prefix('/v1/enrollments')->controller(EnrollmentController::class)->group(function () {
    $this->router->name('index')->get('/', 'index');
    $this->router->name('show')->get('/{id}', 'show');
    $this->router->name('destroy')->delete('/{id}', 'destroy');
});

$this->router->name('api.lessonCompletion.')->prefix('/v1/lessons')->controller(LessonCompletionController::class)->group(function () {
    $this->router->name('show')->get('/{lessonId}/complete/{lessonCompletionId}', 'show');
    $this->router->name('store')->post('/{lessonId}/complete', 'store');
    $this->router->name('update')->patch('/{lessonId}/complete/{lessonCompletionId}', 'update');
    $this->router->name('destroy')->delete('/{lessonId}/uncompleted/{lessonCompletionId}', 'destroy');
});

$this->router->name('api.ratings.')->prefix('/v1/courses')->controller(RatingController::class)->group(function () {
    $this->router->name('index')->get('/{courseId}/ratings', 'index');
    $this->router->name('create')->post('/{courseId}/ratings', 'store');
    $this->router->name('show')->get('/{courseId}/ratings/{ratingId}', 'show');
    $this->router->name('update')->patch('/{courseId}/ratings/{ratingId}', 'update');
    $this->router->name('destroy')->delete('/{courseId}/ratings/{ratingId}', 'destroy');
});

$this->router->name('api.uploads.')->prefix('/v1/uploads')->controller(UploadController::class)->group(function () {
    $this->router->name('profile')->post('/profile', 'storeProfilePhoto');
    $this->router->name('thumbnail')->post('/thumbnail', 'storeCourseThumbnail');
    $this->router->name('video')->post('/video', 'storeCourseVideo');
    $this->router->name('destroy.profile')->delete('/profile', 'destroyProfilePhoto');
    $this->router->name('destroy.thumbnail')->delete('/thumbnail', 'destroyCourseThumbnail');
    $this->router->name('destroy.video')->delete('/video', 'destroyCourseVideo');
});
