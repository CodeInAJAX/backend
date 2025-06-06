<?php

namespace App\Policies;

use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonCompletion;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class LessonCompletionPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, LessonCompletion $lessonCompletion): bool
    {
        $lesson = $lessonCompletion->lesson;
        return $user->enrolledCourses()
            ->where('course_id', $lesson->course_id)
            ->exists();
    }

    /**
     * Determine whether the user can create models.
     */
    public function createLessonCompletion(User $user, Lesson $lesson): bool
    {
        return $user->enrolledCourses()
            ->where('course_id', $lesson->course_id)
            ->exists();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, LessonCompletion $lessonCompletion): bool
    {
        $lesson = $lessonCompletion->lesson;
        return $user->enrolledCourses()
            ->where('course_id', $lesson->course_id)
            ->exists();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, LessonCompletion $lessonCompletion): bool
    {
        $lesson = $lessonCompletion->lesson;
        return $user->enrolledCourses()
            ->where('course_id', $lesson->course_id)
            ->exists();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, LessonCompletion $lessonCompletion): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, LessonCompletion $lessonCompletion): bool
    {
        return false;
    }
}
