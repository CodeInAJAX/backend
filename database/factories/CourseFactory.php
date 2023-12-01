<?php

namespace Database\Factories;

use App\Enums\Role;
use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CourseFactory extends Factory
{
    protected $model = Course::class;

    public function definition(): array
    {
        return [
            'id' => Str::ulid(),
            'title' => $this->faker->sentence(4),
            'thumbnail' => $this->faker->imageUrl(),
            'description' => $this->faker->paragraph(),
            'price' => $this->faker->numberBetween(10000, 100000),
            'currency' => 'IDR',
            'mentor_id' => User::factory()->state(['role' => Role::MENTOR]),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
