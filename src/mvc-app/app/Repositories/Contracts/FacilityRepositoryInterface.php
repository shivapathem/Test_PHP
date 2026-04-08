<?php
namespace App\Repositories\Contracts;

use App\Models\Facility\Facility;
use App\Models\Scheduling\Division;
use App\Models\User;
use Illuminate\Support\Collection;

interface FacilityRepositoryInterface {

    /**
     * Returns facility list
     *
     * @return Collection
     */
    public function getFacilityList(): Collection;

    /**
     * Returns area list
     *
     * @param User $user user
     * @return Collection
     */
    public function getAreaList(?User $user = null): Collection;

    /**
     * Returns team associated to the are list
     *
     * @return Collection
     */
    public function getAreaTeamList(Division $area): Collection;

    /**
     * Save facility
     *
     * @param User $user user
     * @param array $facilityData facility data
     * @param Facility $editFacility Facility to be updated
     *
     * @return Facility
     */
    public function saveFacility(User $user, array $facilityData, ?Facility $editFacility = null): Facility;

    /*
    ** Soft Delete Repository
    */
    public function destroyFacility($facility, User $user): bool;

    /**
     * Archive facility
     */
    public function archiveFacility(array $archiveData, $facility, User $user): bool|string;

     /**
     * Get booking count by after date with new and pending status
     */
    public function countFutureBookingsByStatus($facility, $from , $to): int;

    /**
     * Update Facility Administrator
     * @param Facility $facility, 
     * @param array $formData
     * @param User $user
     */
    public function facilityAdministratorStore(Facility $facility, array $formData, User $user): Facility;
}