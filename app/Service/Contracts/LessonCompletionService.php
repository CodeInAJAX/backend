<?php

namespace App\Service\Contracts;

use App\Http\Requests\StoreLessonCompletionRequest;
use App\Http\Requests\UpdateLessonCompletionRequest;
use App\Http\Resources\LessonCompletionResource;


interface LessonCompletionService
{

    public function show(string $lessonCompletionId) :LessonCompletionResource;

    public function create(StoreLessonCompletionRequest $data, string $lessonId) : LessonCompletionResource;

    public function update(UpdateLessonCompletionRequest $data, string $lessonCompletionId) :LessonCompletionResource;

    public function delete(string $lessonCompletionId) :array;
}
