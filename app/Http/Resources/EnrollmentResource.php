<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

class EnrollmentResource extends JsonResource
{
    public static function collection($resource): AnonymousResourceCollection
    {
        return parent::collection($resource);
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'courseId' => $this->id,
            'studentId' => $this->student_id,
            'status' => $this->status,
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
            'course' => new CourseResource($this->whenLoaded('course')),
            'student' => new UserResource($this->whenLoaded('student')),
            'progress' => $this->calculateProgress()
        ];
    }
}
