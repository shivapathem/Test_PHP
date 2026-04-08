<?php
namespace App\Repositories\Contracts;

use App\Models\Facility\Location;
use App\Models\User;
use Illuminate\Support\Collection;

interface LocationRepositoryInterface {

    /**
     * Returns all the location
     *
     * @return
     */
    public function getAllLocation(): Collection;

    /**
     * Save location
     *
     * @param string $location location name
     * @param User   $user user creating record
     * @return
     */
    public function saveLocation(string $location, User $user): Location;

    /**
     * Update location
     * @param Location $location location model
     * @param string $locationData location name
     * @param User   $user user creating record
     * @return
     */
    public function updateLocation(Location $location, string $locationData, User $user): Location;
}