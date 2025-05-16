<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

class LessonCompletionResource extends JsonResource
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
            'studentId' => $this->student_id,
            'lessonId' => $this->lesson_id,
            'watchDuration' => $this->watch_duration,
            'completedAt' => $this->completed_at,
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
            'lesson' => new LessonResource($this->whenLoaded('lesson')),
            'student' => new UserResource($this->whenLoaded('user')),
        ];
    }
}
