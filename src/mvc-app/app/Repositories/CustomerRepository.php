<?php

namespace App\Repositories;

use App\Models\FacilityBooking\ExternalCustomer;
use App\Models\FacilityBooking\FacilityBooking;
use App\Models\User;
use App\Repositories\Contracts\CustomerRepositoryInterface;
use Illuminate\Support\Collection;

class CustomerRepository implements CustomerRepositoryInterface
{
    /**
     * Returns all the Customer
     *
     * @return
     */
    public function getAllCustomer(): Collection
    {
        return ExternalCustomer::all();
    }

    /**
     * Save Customer
     *
     * @param User   $user user creating record
     * @return
     */
    public function saveCustomer(array $customerData, User $user): ExternalCustomer
    {
        $customerModel = new ExternalCustomer();
        $customerMainData = $customerData['customerFormData'];
        $CustomerAddressLocalForm = $customerData['CustomerAddressLocalForm'] ?? null;
        $CustomerAddressInternationalForm = $customerData['CustomerAddressInternationalForm'] ?? null;
        $customerModel->EC_CompanyName = $customerMainData['ec_companyname'];
        $customerModel->EC_BuildingNumber = $CustomerAddressLocalForm['ec_buildingnumber'];
        $customerModel->EC_Street = $CustomerAddressLocalForm['ec_street'];
        $customerModel->EC_City = $CustomerAddressLocalForm['ec_city'];
        $customerModel->EC_County = $CustomerAddressLocalForm['ec_county'];
        $customerModel->EC_Country = $CustomerAddressLocalForm['ec_country'];
        $customerModel->EC_InternationalAddress = $CustomerAddressInternationalForm['ec_internationaladdress'] ?? null;
        $customerModel->EC_ContactName = $customerMainData['ec_contactname'];
        $customerModel->EC_Position = $customerMainData['ec_position'];
        $customerModel->EC_ContactNumber = $customerMainData['ec_telephonenumber'];
        $customerModel->EC_PostCode = $CustomerAddressLocalForm['ec_postcode'];
        $customerModel->EC_ContactEmail = $customerMainData['ec_email'];
        $customerModel->EC_CreatedBy = $user->UD_UserID;
        $customerModel->EC_UpdatedBy = $user->UD_UserID;
        $customerModel->save();
        return  $customerModel;
    }
    /**
     * Save Customer
     *
     * @param User   $user user creating record
     * @return
     */
    public function updateCustomer(ExternalCustomer $customer, array $customerData, User $user): ExternalCustomer
    {
        $customerModel = $customer;
        $customerMainData = $customerData['customerFormData'];
        $CustomerAddressLocalForm = $customerData['CustomerAddressLocalForm'] ?? null;
        $CustomerAddressInternationalForm = $customerData['CustomerAddressInternationalForm'] ?? null;
        $customerModel->EC_CompanyName = $customerMainData['ec_companyname'];
        $customerModel->EC_BuildingNumber = $CustomerAddressLocalForm['ec_buildingnumber'];
        $customerModel->EC_Street = $CustomerAddressLocalForm['ec_street'];
        $customerModel->EC_City = $CustomerAddressLocalForm['ec_city'];
        $customerModel->EC_County = $CustomerAddressLocalForm['ec_county'];
        $customerModel->EC_Country = $CustomerAddressLocalForm['ec_country'];
        $customerModel->EC_InternationalAddress = $CustomerAddressInternationalForm['ec_internationaladdress'] ?? null;
        $customerModel->EC_ContactName = $customerMainData['ec_contactname'];
        $customerModel->EC_Position = $customerMainData['ec_position'];
        $customerModel->EC_ContactNumber = $customerMainData['ec_telephonenumber'];
        $customerModel->EC_PostCode = $CustomerAddressLocalForm['ec_postcode'];
        $customerModel->EC_ContactEmail = $customerMainData['ec_email'];
        $customerModel->EC_UpdatedBy = $user->UD_UserID;
        $customerModel->save();
        return  $customerModel;
    }


    public function destroyCustomer($customer, $user): bool
    {
        $customer->delete();
        return true;
    }
}
