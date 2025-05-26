<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

class RatingResource extends JsonResource
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
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Check if this is coming from a pivot relationship
        if (isset($this->pivot)) {
            return [
                'id' => $this->id,
                'name' => $this->name ?? null,
                'email' => $this->email ?? null,
                'role' => $this->role ?? null,
                'profile' => isset($this->profile) ? [
                    'gender' => $this->profile->gender ?? null,
                    'about' => $this->profile->about ?? null,
                    'photo' => $this->profile->photo ?? null,
                ] : null,
                'rating' => $this->pivot->rating,
                'comment' => $this->pivot->comment,
                'createdAt' => $this->pivot->created_at,
                'updatedAt' => $this->pivot->updated_at,
            ];
        }

        // Original implementation for direct Rating model
        return [
            'id' => $this->id,
            'userId' => $this->user_id,
            'courseId' => $this->course_id,
            'rating' => $this->rating,
            'comment' => $this->comment,
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
            'user' => new UserResource($this->whenLoaded('user')),
            'course' => new CourseResource($this->whenLoaded('course')),
        ];
    }
}
