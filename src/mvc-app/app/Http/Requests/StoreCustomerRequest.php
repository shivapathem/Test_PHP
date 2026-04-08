<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCustomerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'customerFormData' => "required|array",
            "customerFormData.ec_contactname" => [
                "required",
                Rule::unique('ExternalCustomers', 'EC_ContactName')->where(function ($query) {
                    $query->where('EC_CompanyName', $this->customerFormData['ec_companyname'])
                        ->where('EC_Position', $this->customerFormData['ec_position'])
                        ->whereNull('deleted_at');
                    if ($this->route('customer') != null) {
                        $query->where('EC_ExternalCustomerID', '!=', $this->route('customer')->EC_ExternalCustomerID);
                    }
                    return $query;
                })
            ],
            "customerFormData.ec_telephonenumber" => "required|numeric|regex:/^([0-9\s\-\+\(\)]{10,15})$/|min:10",
            "customerFormData.ec_email" => "required|max:60|regex:/^(?!.*\.\.)([a-zA-Z0-9._%+-]+)@([a-zA-Z0-9-]+\.)+[a-zA-Z]{2,}$/",
            "customerFormData.ec_companyname" => [
                "required"
            ]
        ];
    }

    /**
     * Attributes
     */
    public function attributes()
    {
        return [
            "customerFormData.ec_contactname" => "Contact Name",
            "customerFormData.ec_telephonenumber" => "Telephone Number",
            "customerFormData.ec_email" => "Email Address",
            "customerFormData.ec_companyname" => "Company Name",
            "customerAddressLocalForm" . "ec_buildingnumber" => "Building Number",
            "customerAddressLocalForm" . "ec_street" => "Street",
            "customerAddressLocalForm" . "ec_country" => "Country",
            "customerAddressLocalForm" . "ec_city" => "City",
            "CustomerAddressInternationalForm" . "ec_internationaladdress" => "International Address"
        ];
    }

    /**
     * Message
     */
    public function messages()
    {
        return [
            'customerFormData.ec_contactname.unique' => 'The combination of Contact Name, Position, Company Name has already been taken.'
        ];
    }
}
