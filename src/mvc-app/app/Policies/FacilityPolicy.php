<?php

namespace App\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use App\Models\Facility\Facility;
use App\Models\User;
use Carbon\Carbon;

class FacilityPolicy
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
        return ($user->isFacilityAdministrator == 1) || ($user->isDivisionalAdmin == 1);
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Facility\Facility  $facility
     * @return mixed
     */
    public function view(User $user, Facility $facility)
    {
        //
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return $user->isRealFacilityAdministrator == 1;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Facility\Facility  $facility
     * @return mixed
     */
    public function update(User $user, Facility $facility)
    {
        $accessible = $this->viewAny($user);
        if ($facility->FC_ArchivedDate != null && $facility->FC_ArchivedDate->lte(Carbon::now())) {
            $accessible = false;
        }
        return $accessible;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Facility\Facility  $facility
     * @return mixed
     */
    public function facilityAdminReal(User $user, Facility $facility)
    {
        return $user->isFacilityAdministrator($facility, 1);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Facility\Facility  $facility
     * @return mixed
     */
    public function delete(User $user, Facility $facility)
    {
        //
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Facility\Facility  $facility
     * @return mixed
     */
    public function restore(User $user, Facility $facility)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Facility\Facility  $facility
     * @return mixed
     */
    public function forceDelete(User $user, Facility $facility)
    {
        //
    }
}
