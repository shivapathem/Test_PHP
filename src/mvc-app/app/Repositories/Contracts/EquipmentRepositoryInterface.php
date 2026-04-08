<?php
namespace App\Repositories\Contracts;

use App\Models\Facility\Equipment;
use App\Models\User;
use Illuminate\Support\Collection;

interface EquipmentRepositoryInterface {

    /**
     * Returns all the equipment
     *
     * @return
     */
    public function getAllEquipment(): Collection;

    /**
     * Save equipment
     *
     * @param string $equipment equipment name
     * @param User   $user user creating record
     * @return
     */
    public function saveEquipment(string $equipment, User $user): Equipment;

    /**
     * Update equipment
     *
     * @param Equipment $equipment equipment
     * @param string $equipment equipment name
     * @param User   $user user creating record
     * @return
     */
    public function updateEquipment(Equipment $equipment, string $equipmentData, User $user): Equipment;

    /**
     * Get list of Equipments
     *
     * @return array
     */
    public function equipmentList(): array;
}