<?php

namespace App\Policies;

use App\Enums\Status;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class LessonPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    protected function isCourseAccessibleByStudent(User $user, Course $course): bool
    {
        return $user->hasRole('student') &&
            $user->enrolledCourses()
                ->where('course_id', $course->id)
                ->where('status', Status::ACTIVE->value)
                ->exists();
    }


    /**
     * Determine whether the user can view all lessons in the given course.
     */
    public function viewAllByCourse(User $user, Course $course): bool
    {
        return (
                ($user->hasRole('mentor') || $user->hasRole('admin')) && $course->mentor_id == $user->id
            ) || (
                $this->isCourseAccessibleByStudent($user,$course)
            );
    }


    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Lesson $lesson): bool
    {
        $course = $lesson->course;

        return (
                ($user->hasRole('mentor') || $user->hasRole('admin')) && $course->mentor_id == $user->id
            ) || (
                $this->isCourseAccessibleByStudent($user,$course)
            );
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return ($user->hasRole('mentor') || $user->hasRole('admin'));
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Lesson $lesson): bool
    {
        $course = $lesson->course;
        return ($user->hasRole('mentor') || $user->hasRole('admin')) && $course->mentor_id == $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Lesson $lesson): bool
    {
        $course = $lesson->course;
        return ($user->hasRole('mentor') || $user->hasRole('admin')) && $course->mentor_id == $user->id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Lesson $lesson): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Lesson $lesson): bool
    {
        return false;
    }
}
