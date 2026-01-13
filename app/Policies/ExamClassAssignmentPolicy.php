<?php

namespace App\Policies;

use App\Models\ExamClassAssignment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ExamClassAssignmentPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('exam_class_assignments.view');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ExamClassAssignment $assignment): bool
    {
        return $user->hasPermissionTo('exam_class_assignments.view') &&
               $assignment->company_id === $user->company_id &&
               ($assignment->branch_id === $user->branch_id || $user->hasRole('super-admin'));
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('exam_class_assignments.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ExamClassAssignment $assignment): bool
    {
        return $user->hasPermissionTo('exam_class_assignments.edit') &&
               $assignment->company_id === $user->company_id &&
               ($assignment->branch_id === $user->branch_id || $user->hasRole('super-admin'));
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ExamClassAssignment $assignment): bool
    {
        return $user->hasPermissionTo('exam_class_assignments.delete') &&
               $assignment->company_id === $user->company_id &&
               ($assignment->branch_id === $user->branch_id || $assignment->branch_id === null || $user->hasRole('super-admin') || $user->hasRole('admin'));
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ExamClassAssignment $assignment): bool
    {
        return $user->hasPermissionTo('exam_class_assignments.restore') &&
               $assignment->company_id === $user->company_id &&
               ($assignment->branch_id === $user->branch_id || $user->hasRole('super-admin'));
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ExamClassAssignment $assignment): bool
    {
        return $user->hasPermissionTo('exam_class_assignments.force_delete') &&
               $assignment->company_id === $user->company_id &&
               ($assignment->branch_id === $user->branch_id || $user->hasRole('super-admin'));
    }
}