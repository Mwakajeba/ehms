<?php

namespace App\Policies;

use App\Models\OtherIncome;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class OtherIncomePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user)
    {
        return $user->hasPermissionTo('view-other-income');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, OtherIncome $otherIncome)
    {
        return $user->company_id === $otherIncome->company_id &&
               $user->hasPermissionTo('view-other-income');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user)
    {
        return $user->hasPermissionTo('create-other-income');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, OtherIncome $otherIncome)
    {
        return $user->company_id === $otherIncome->company_id &&
               $otherIncome->status === 'pending' &&
               $user->hasPermissionTo('edit-other-income');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, OtherIncome $otherIncome)
    {
        return $user->company_id === $otherIncome->company_id &&
               $otherIncome->status !== 'approved' &&
               $user->hasPermissionTo('delete-other-income');
    }

    /**
     * Determine whether the user can approve the model.
     */
    public function approve(User $user, OtherIncome $otherIncome)
    {
        return $user->company_id === $otherIncome->company_id &&
               $otherIncome->status === 'pending' &&
               $user->hasPermissionTo('approve-other-income');
    }
}