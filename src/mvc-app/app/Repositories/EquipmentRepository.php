<?php

namespace App\Repositories;

use App\Models\Facility\Equipment;
use App\Models\User;
use App\Repositories\Contracts\EquipmentRepositoryInterface;
use Illuminate\Support\Collection;

class EquipmentRepository implements EquipmentRepositoryInterface
{
      /**
     * Returns all the equipment
     *
     * @return
     */
    public function getAllEquipment(): Collection
    {
        return Equipment::all();
    }

    /**
     * Save equipment
     *
     * @param string $equipment equipment name
     * @param User   $user user creating record
     * @return
     */
    public function saveEquipment($equipmentData, User $user): Equipment
    {
        $equipmentModel = new Equipment();
        $equipment = $equipmentData['equipment'];
        $equipment_type = $equipmentData['equipment_type'];
        $equipmentModel->EQ_Equipment = $equipment;
        $equipmentModel->EQ_Equipment_Type = $equipment_type;
        $equipmentModel->EQ_CreatedBy = $user->UD_UserID;
        $equipmentModel->EQ_UpdatedBy = $user->UD_UserID;
        $equipmentModel->save();
        return  $equipmentModel;
    }

    /**
     * Update equipment
     *
     * @param Equipment $equipment equipment
     * @param string $equipment equipment name
     * @param User   $user user creating record
     * @return
     */
    public function updateEquipment(Equipment $equipment, $equipmentData, User $user): Equipment
    {
        $equipmentModel = $equipment;
        $equipmentModel->EQ_Equipment = $equipmentData['equipment'];
        $equipmentModel->EQ_Equipment_Type = $equipmentData['equipment_type'];
        $equipmentModel->EQ_CreatedBy = $user->UD_UserID;
        $equipmentModel->EQ_UpdatedBy = $user->UD_UserID;
        $equipmentModel->save();
        return  $equipmentModel;
    }

    public function destroyEquipment($equipment, User $user): Equipment
    {
        $equipment_id = Equipment::find($equipment->EQ_EquipmentID);
        $equipment_id->delete();
        return $equipment_id;
    }
    /**
     * Get list of Equipments
     *
     * @return array
     */
    public function equipmentList(): array
    {
        return Equipment::select('EQ_Equipment', 'EQ_EquipmentID')->get()->toArray();
    }
}
