<?php

namespace App\Http\Requests;

use App\Rules\RuleDeleteFacilityType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DeleteFacilityTypeRequest extends FormRequest
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
            'facility_type' => [
                'required',
                new RuleDeleteFacilityType($this->route('facility_type'))
            ]
        ];
    }
}
