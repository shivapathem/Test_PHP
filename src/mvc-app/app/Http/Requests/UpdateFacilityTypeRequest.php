<?php

namespace App\Http\Requests;

use App\Rules\RuleFacilitySubTypeUpdate;
use Illuminate\Validation\Rule;

class UpdateFacilityTypeRequest extends StoreFacilityTypeRequest
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
        $rules = parent::rules();
        $rules['facility_sub_types'] = [
            'required',
            'array',
            new RuleFacilitySubTypeUpdate($this->route('facility_type'))
        ];
        return $rules;
    }
}
