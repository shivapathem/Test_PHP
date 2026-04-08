<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreServiceRequest extends FormRequest
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
        $edit = $this->route('service') != null ? $this->route('service')->SR_ServiceID : 0;
        return [
            'service' => [
                'required',
                'max:50',
                Rule::unique('Services', 'SR_Service')->whereNull('deleted_at')->ignore($edit, 'SR_ServiceID')
            ]
        ];
    }
}
