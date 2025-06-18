<?php

namespace App\Service\Contracts;

use App\Http\Requests\PaginationRequest;
use App\Http\Requests\StoreLessonRequest;
use App\Http\Requests\UpdateLessonRequest;
use App\Http\Resources\LessonResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

interface LessonService
{
    public function getAll(PaginationRequest $data, string $courseId) : AnonymousResourceCollection;

    public function show(string $courseId, string $lessonId) : LessonResource;

    public function create(StoreLessonRequest $data, string $courseId) : LessonResource;

    public function update(UpdateLessonRequest $data, string $courseId, string $lessonId) : LessonResource;

    public function delete(string $courseId, string $lessonId) : array;
}
