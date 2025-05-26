<?php

namespace App\Service\Contracts;

use App\Http\Requests\PaginationRequest;
use App\Http\Requests\SearchPaginationRequest;
use App\Http\Requests\StoreCourseRequest;
use App\Http\Requests\UpdateCourseRequest;
use App\Http\Resources\CourseResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

interface CourseService
{
    public function getAll(PaginationRequest $data) : AnonymousResourceCollection;

    public function search(SearchPaginationRequest $data) : AnonymousResourceCollection;

    public function detail(string $id) : CourseResource;

    public function create(StoreCourseRequest $data) : CourseResource;

    public function update(string $id,UpdateCourseRequest $data) : CourseResource;

    public function delete(string $id) : array;
}
