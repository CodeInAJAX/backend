<?php

namespace App\Policies;

use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Collection;

class EnrollmentPolicy
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
    public function view(User $user, Enrollment $enrollment): bool
    {
        $course = $enrollment->course;
        return (($user->hasRole('admin') || $user->hasRole('mentor')) && $course->mentor_id == $user->id) || ($user->hasRole('student') && $user->id == $enrollment->student_id);
    }

    public function viewAll(User $user, Enrollment $enrollment, Collection $enrollments): bool
    {
        return $enrollments->every(function ($enrollment) use ($user) {
            return
                $enrollment->student_id === $user->id;
        });
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('student');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Enrollment $enrollment): bool
    {

        $course = $enrollment->course;
        return (($user->hasRole('admin') || $user->hasRole('mentor')) && $course->mentor_id == $user->id);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Enrollment $enrollment): bool
    {

        $course = $enrollment->course;
        return (($user->hasRole('admin') || $user->hasRole('mentor')) && $course->mentor_id == $user->id) || ($user->hasRole('student') && $user->id == $enrollment->student_id);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Enrollment $enrollment): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Enrollment $enrollment): bool
    {
        return false;
    }
}
