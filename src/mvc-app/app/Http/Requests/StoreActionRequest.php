<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreActionRequest extends FormRequest
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
        $edit = $this->route('action') != null ? $this->route('action')->action_id : 0;
        return [
            'action_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('Actions', 'action_name')->whereNull('deleted_at')->ignore($edit, 'action_id')
            ],
            'description' => ['required', 'string']
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'action_name.required' => 'The action name is required.',
            'action_name.max' => 'The action name must not exceed 255 characters.',
            'description.required' => 'The description is required.',
        ];
    }
}
