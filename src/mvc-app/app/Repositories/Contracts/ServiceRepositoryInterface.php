<?php
namespace App\Repositories\Contracts;

use App\Models\Facility\Service;
use App\Models\User;
use Illuminate\Support\Collection;

interface ServiceRepositoryInterface {

    /**
     * Returns all the service
     *
     * @return
     */
    public function getAllService(): Collection;

    /**
     * Save service
     *
     * @param string $service service name
     * @param User   $user user creating record
     * @return
     */
    public function saveService(string $service, User $user): Service;

    /**
     * update service
     *
     * @param string $service service name
     * @param User   $user user creating record
     * @return Service
     */
    public function updateService(Service $service, string $serviceData, User $user): Service;

    /**
     * Facility SerivceList
     * @return array
     */
    public function facilitySerivceList(): array;
}