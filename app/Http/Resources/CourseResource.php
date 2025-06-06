<?php

namespace App\Http\Resources;


use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;


class CourseResource extends JsonResource
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
            'title' => $this->title,
            'thumbnail' => $this->thumbnail,
            'description' => $this->description,
            'price' => $this->price,
            'currency' => $this->currency,
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
            'mentor' => new UserResource($this->whenLoaded('mentor')),
            'lessons' => LessonResource::collection($this->whenLoaded('lessons')),
            'payments' => PaymentResource::collection($this->whenLoaded('payments')),
            'ratingsUsers' => RatingResource::collection($this->whenLoaded('ratingsUsers')),
            'students' => UserResource::collection($this->whenLoaded('students')),
        ];
    }
}
