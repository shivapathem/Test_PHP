<?php

namespace App\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use App\Models\Scheduling\SchedulingGroup;
use App\Models\User;

class SchedulingGroupPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        return $user->isSystemAdmin || $user->isDivisionalAdmin;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Scheduling\SchedulingGroup  $schedulingGroup
     * @return mixed
     */
    public function view(User $user, SchedulingGroup $schedulingGroup)
    {
        if ($user->isSystemAdmin) {
            return true;
        }

        $userDivisionIds = $user->getAreasRoles->unique('DivisionID')->pluck('DivisionID');

        return $userDivisionIds->contains($schedulingGroup->DivisionID);
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return $user->isSystemAdmin || $user->isDivisionalAdmin;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Scheduling\SchedulingGroup  $schedulingGroup
     * @return mixed
     */
    public function update(User $user, SchedulingGroup $schedulingGroup)
    {
        return $this->view($user, $schedulingGroup);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Scheduling\SchedulingGroup  $schedulingGroup
     * @return mixed
     */
    public function delete(User $user, SchedulingGroup $schedulingGroup)
    {
        return $this->view($user, $schedulingGroup);
    }
}
