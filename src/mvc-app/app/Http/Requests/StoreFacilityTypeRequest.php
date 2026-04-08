<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFacilityTypeRequest extends FormRequest
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
        $edit = $this->route('facility_type') != null ? $this->route('facility_type')->FT_FacilityTypeID : 0;
        return [
            'facility_type' => [
                'required',
                'max:50',
                Rule::unique('FacilityTypes', 'FT_FacilityType')->whereNull('deleted_at')->ignore(
                    $edit,
                    'FT_FacilityTypeID'
                )
            ],
            'facility_sub_types' => 'required|array|min:1'
        ];
    }
}
