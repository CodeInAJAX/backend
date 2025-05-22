<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
use InvalidArgumentException;

/**
 * @property-read User $resource
 */
class UserResource extends JsonResource
{
    public function __construct($resource)
    {
        parent::__construct($resource);
    }

    public static function collection($resource): AnonymousResourceCollection
    {
        return parent::collection($resource);
    }


    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'profile' => [
                'gender' => $this->profile->gender,
                'about' => $this->profile->about,
                'photo' => $this->profile->photo,
            ],
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
            'courses' => CourseResource::collection($this->whenLoaded('courses')),
        ];
    }
}
