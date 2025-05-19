<?php

namespace App\Service\Contracts;

use App\Http\Requests\PaginationRequest;
use App\Http\Requests\StoreLessonCompletionRequest;
use App\Http\Requests\UpdateLessonCompletionRequest;
use App\Models\LessonCompletion;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

interface LessonCompletionService
{
    public function index(PaginationRequest $data) :AnonymousResourceCollection;

    public function show(string $id) :LessonCompletion;

    public function create(StoreLessonCompletionRequest $data) : LessonCompletion;

    public function update(UpdateLessonCompletionRequest $data, string $id) :LessonCompletion;

    public function delete(string $id) :array;
}
