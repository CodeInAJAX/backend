<?php

namespace App\Http\Resources;

use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use InvalidArgumentException;

class LessonResource extends JsonResource
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
            "id" => $this->id,
            "title" => $this->title,
            "description" => $this->description,
            "videoLink" => $this->video_link,
            "duration" => $this->duration,
            "orderNumber" => $this->order_number,
            "createdAt" => $this->created_at,
            "updatedAt" => $this->updated_at,
            "course" => new CourseResource($this->whenLoaded('course'))
        ];
    }
}
