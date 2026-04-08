<?php
namespace App\Repositories\Contracts;

use App\Models\FacilityBooking\ExternalCustomer;
use App\Models\User;
use Illuminate\Support\Collection;
interface CustomerRepositoryInterface {

    /**
     * Returns all the Customer
     *
     * @return
     */
    public function getAllCustomer(): Collection;
    public function saveCustomer(array $customer, User $user): ExternalCustomer;

    public function updateCustomer(ExternalCustomer $customer, array $customerData, User $user): ExternalCustomer;

    public function destroyCustomer(ExternalCustomer $customer, $user): bool;
}