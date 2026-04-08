<?php

namespace App\Repositories;

use App\Models\Facility\FacilitySubType;
use App\Models\Facility\FacilityType;
use App\Models\User;
use App\Repositories\Contracts\FacilityTypeRepositoryInterface;
use Illuminate\Support\Collection;

class FacilityTypeRepository implements FacilityTypeRepositoryInterface
{
    /**
     * Returns all the facility sub type
     *
     * @return
     */
    public function getAllFacilityType(): Collection
    {
        return FacilityType::with('facilitySubType')->get();
    }

    /**
     * Save facility type
     *
     * @param string $facilityType facility type name
     * @param array $facilitySubTypes facility sub types
     * @param User   $user user creating record
     * @return
     */
    public function saveFacilityType(string $facilityType, array $facilitySubTypes, User $user): FacilityType
    {
        $facilityTypeModel = new FacilityType();
        $facilityTypeModel->FT_FacilityType = $facilityType;
        $facilityTypeModel->FT_CreatedBy = $user->UD_UserID;
        $facilityTypeModel->FT_UpdatedBy = $user->UD_UserID;
        $facilityTypeModel->save();
        $facilitySubTypesModel = [];
        foreach ($facilitySubTypes as $facilitySubType) {
            $facilitySubTypesModel[] =  new FacilitySubType(['FST_FacilitySubType' => $facilitySubType]);
        }
        $facilityTypeModel->facilitySubType()->saveMany($facilitySubTypesModel);
        return  $facilityTypeModel;
    }

    /**
     * Update facility type
     * @param FacilityType $facilitytype model
     * @param string $facility type name
     * @param array $facilitySubTypes facility sub types
     * @param User   $user user creating record
     * @return
     */
    public function updateFacilityType(FacilityType $facilitytype, string $facilitytypeData, array $facilitySubTypes, User $user): FacilityType
    {
        $facilityTypeModel = $facilitytype;
        $facilityTypeModel->FT_FacilityType = $facilitytypeData;
        $facilityTypeModel->FT_UpdatedBy = $user->UD_UserID;
        $facilityTypeModel->FT_UpdatedDate = now();
        $facilityTypeModel->save();
        $facilitySubTypesModel = [];
        $existingFacilitySubTypes = $facilitytype->facilitySubType;
        foreach ($facilitySubTypes as $facilitySubType) {
            if ($existingFacilitySubTypes->where('FST_FacilitySubType', $facilitySubType)->count() > 0) {  //Facility Type Already Exists
                continue;
            }
            $facilitySubTypesModel[] =  new FacilitySubType(['FST_FacilitySubType' => $facilitySubType]);
        }
        if (!empty($facilitySubTypesModel)) {
            $facilityTypeModel->facilitySubType()->saveMany($facilitySubTypesModel);
        }
        //Delete removed facility sub types
        $existingFacilitySubTypesRemoved = $facilitytype->facilitySubType->whereNotIn('FST_FacilitySubType', $facilitySubTypes);
        foreach ($existingFacilitySubTypesRemoved as $facilitySubTypesRemoved) {
            $facilitySubTypesRemoved->delete();
        }

        return  $facilityTypeModel;
    }
    public function destroyFacilitiyType($facilityType, User $user): FacilityType
    {
        $facilitytype_id = FacilityType::find($facilityType->FT_FacilityTypeID);
        $facilitytype_id->delete();
        return $facilitytype_id;
    }

    /**
     * Get list of facility type
     */
    public function facilityTypeList(): array
    {
        return FacilityType::select('FT_FacilityTypeID', 'FT_FacilityType')->get()->toArray();
    }

    /**
     * Get list of facility sub type
     *
     * @param array $facilityTypeIds facility type ids
     */
    public function facilitySubTypeList(array $facilityTypeIds): array
    {
        $returnData = [];
        foreach (FacilityType::with('facilitySubType')->whereIn('FT_FacilityTypeID', $facilityTypeIds)->get() as $facilityType) {
            $returnData[$facilityType->FT_FacilityType] = $facilityType->facilitySubType->pluck('FST_FacilitySubType', 'FST_FacilitySubTypeID');
        }
        return $returnData;
    }
}
