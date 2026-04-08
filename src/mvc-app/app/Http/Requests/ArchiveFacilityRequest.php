<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ArchiveFacilityRequest extends FormRequest
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
        $rules = [
            'change_date' => [
                'required',
                'date_format:d/m/Y',
                'after_or_equal:' . now()->format('d/m/Y')
            ]
        ];

        if ($this->input('change_status') === 'archive') {
            $facility = $this->route('facility');
            if ($facility && $facility->FC_ActiveFrom) {
                $activeFromDate = $facility->FC_ActiveFrom->format('d/m/Y');
                $rules['change_date'][] = 'after_or_equal:' . $activeFromDate;
            }
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        $messages = [];

        if ($this->input('change_status') === 'archive') {
            $facility = $this->route('facility');
            if ($facility && $facility->FC_ActiveFrom) {
                $activeFromDate = $facility->FC_ActiveFrom->format('d/m/Y');
                $messages['change_date.after_or_equal'] = 'The archive date cannot be before the facility Active From date (' . $activeFromDate . ').';
            }
        }

        return $messages;
    }
}
