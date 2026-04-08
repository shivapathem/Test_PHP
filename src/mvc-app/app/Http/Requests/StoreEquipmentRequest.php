<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEquipmentRequest extends FormRequest
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
        $edit = $this->route('equipment') != null ? $this->route('equipment')->EQ_EquipmentID : 0;
        return [
            'equipment' => [
                'required',
                'max:50',
                Rule::unique('Equipment', 'EQ_Equipment')->whereNull('deleted_at')->ignore($edit, 'EQ_EquipmentID')
            ],
            'equipment_type' => [
                'required'
            ]
        ];
    }

    /**
     * Equipment attributes name
     */
    public function attributes()
    {
        return [
            'equipment' => 'Equipment',
            'equipment_type' => 'Equipment Type',
        ];
    }
}
