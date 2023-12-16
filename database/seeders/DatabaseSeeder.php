<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\Profile;
use App\Models\Rating;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        try {
            User::factory(10)->create();
            Course::factory(10)->create();
            Profile::factory(10)->create();
            Lesson::factory(10)->create();
            Enrollment::factory(10)->create();
            Rating::factory(10)->create();
        } catch (\Exception $error) {
            echo $error->getMessage();
        }
    }
}
