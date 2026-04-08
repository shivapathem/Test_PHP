<?php

namespace App\Repositories\Contracts;

use App\Models\Facility\FacilityType;
use App\Models\User;
use Illuminate\Support\Collection;

interface FacilityTypeRepositoryInterface
{
    /**
     * Returns all the facility type
     *
     * @return
     */
    public function getAllFacilityType(): Collection;

    /**
     * Save facility  type
     *
     * @param string $facilityType facility type name
     * @param array $facilitySubTypes facility sub types
     * @param User   $user user creating record
     * @return
     */
    public function saveFacilityType(string $facilityType, array $facilitySubTypes, User $user): FacilityType;

    /**
     * Update facility type
     * @param FacilityType $facilitytype model
     * @param string $facility type name
     * @param array $facilitySubTypes facility sub types
     * @param User   $user user creating record
     * @return
     */
    public function updateFacilityType(FacilityType $facilitytype, string $facilitytypeData, array $facilitySubTypes, User $user): FacilityType;

    /**
     * Get list of facility
     */
    public function facilityTypeList(): array;

    /**
     * Get list of facility sub type
     *
     * @param array $facilityTypeIds facility type ids
     */
    public function facilitySubTypeList(array $facilityTypeIds): array;
}
