<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\Rating;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class RatingPolicy
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
    public function view(User $user, Rating $rating): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function createRating(User $user, Course $course): bool
    {
        return (
            $user->hasRole('student')
            && $user->enrolledCourses()
                ->where('course_id', $course->id)
                ->exists()
        );
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Rating $rating): bool
    {
        return (
            $user->hasRole('student')
            && $user->enrolledCourses()
                ->where('course_id', $rating->course_id)
                ->exists()
        );
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Rating $rating): bool
    {
        return (
            $user->hasRole('student')
            && $user->enrolledCourses()
                ->where('course_id', $rating->course_id)
                ->exists()
        );
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Rating $rating): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Rating $rating): bool
    {
        return false;
    }
}
