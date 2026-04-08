<?php

namespace App\Repositories;

use App\Models\Facility\Location;
use App\Models\User;
use App\Repositories\Contracts\LocationRepositoryInterface;
use Illuminate\Support\Collection;

class LocationRepository implements LocationRepositoryInterface
{
      /**
     * Returns all the location
     *
     * @return
     */
    public function getAllLocation(): Collection
    {
        return Location::all();
    }

    /**
     * Save location
     *
     * @param string $location location name
     * @param User   $user user creating record
     * @return
     */
    public function saveLocation(string $location, User $user): Location
    {
        $locationModel = new Location();
        $locationModel->LN_Location = $location;
        $locationModel->LN_CreatedBy = $user->UD_UserID;
        $locationModel->LN_UpdatedBy = $user->UD_UserID;
        $locationModel->save();
        return  $locationModel;
    }

    /**
     * Update location
     * @param Location $location location model
     * @param string $locationData location name
     * @param User   $user user creating record
     * @return
     */
    public function updateLocation(Location $location, string $locationData, User $user): Location
    {
        $locationModel = $location;
        $locationModel->LN_Location = $locationData;
        $locationModel->LN_UpdatedBy = $user->UD_UserID;
        $locationModel->save();
        return  $locationModel;
    }
    /**
     * delete location
     *
     * @param string $location location name
     * @param User   $user user creating record
     * @return
     */
    public function destroyLocation($location, User $user): Location
    {
        $location_id = Location::find($location->LN_LocationID);
        $location_id->delete();
        return $location_id;
    }
}
