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
        if (! $resource instanceof User) {
            throw new InvalidArgumentException('UserResource only accepts instances of ' . User::class);
        }

        parent::__construct($resource);
    }

    public static function collection($resource): AnonymousResourceCollection
    {
        if (! $resource instanceof Collection || $resource->first() instanceof User) {
            return parent::collection($resource);
        }

        throw new InvalidArgumentException('UserResource::collection only accepts instances of ' . User::class);
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
            'updatedAt' => $this->updated_at
        ];
    }
}
