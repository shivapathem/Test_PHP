<?php

namespace App\Repositories;

use App\Models\Facility\Service;
use App\Models\User;
use App\Repositories\Contracts\ServiceRepositoryInterface;
use Illuminate\Support\Collection;

class ServiceRepository implements ServiceRepositoryInterface
{
      /**
     * Returns all the service
     *
     * @return
     */
    public function getAllService(): Collection
    {
        return Service::all();
    }

    /**
     * Save service
     *
     * @param string $service service name
     * @param User   $user user creating record
     * @return
     */
    public function saveService(string $service, User $user): Service
    {
        $serviceModel = new Service();
        $serviceModel->SR_Service = $service;
        $serviceModel->SR_CreatedBy = $user->UD_UserID;
        $serviceModel->SR_UpdatedBy = $user->UD_UserID;
        $serviceModel->save();
        return  $serviceModel;
    }
    /**
     * update service
     *
     * @param string $service service name
     * @param User   $user user creating record
     * @return
     */
    public function updateService(Service $service, string $serviceData, User $user): Service
    {
        $serviceModel = $service;
        $serviceModel->SR_Service = $serviceData;
        $serviceModel->SR_UpdatedBy = $user->UD_UserID;
        $serviceModel->save();
        return  $serviceModel;
    }
    /**
     * delete service
     *
     * @param string $service service name
     * @param User   $user user creating record
     * @return
     */
    public function destroyService($service, User $user): Service
    {
        $service_id = Service::find($service->SR_ServiceID);
        $service_id->delete();
        return $service_id;
    }

    /**
     * Facility SerivceList
     * @return array
     */
    public function facilitySerivceList(): array
    {
        return Service::select('SR_Service', 'SR_ServiceID')->get()->toArray();
    }
}
