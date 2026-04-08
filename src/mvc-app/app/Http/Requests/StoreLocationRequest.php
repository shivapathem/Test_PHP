<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLocationRequest extends FormRequest
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
        $edit = $this->route('location') != null ? $this->route('location')->LN_LocationID : 0;
        return [
            'location' => [
                'required',
                'max:50',
                Rule::unique('Locations', 'LN_Location')->whereNull('deleted_at')->ignore($edit, 'LN_LocationID')
            ]
        ];
    }
}
