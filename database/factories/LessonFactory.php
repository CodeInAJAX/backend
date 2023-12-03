<?php

namespace Database\Factories;

use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Lesson>
 */
class LessonFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(),
            'description' => $this->faker->paragraph(),
            'video_link' => $this->faker->url(),
            'duration' => $this->faker->randomDigit(),
            'order_number' => $this->faker->randomDigit(),
            'course_id' => Course::factory()->create(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
