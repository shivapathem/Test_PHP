<?php

namespace App\Http\Requests;

use App\Rules\SaveFilterRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFilterRequest extends FormRequest
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
            'filterId' => ['nullable'],
            'filterData' => ['required', 'array'],
            'filterName' => [
                'required',
                new SaveFilterRequest($this->filterType, $this->filterPrivacyType, $this->filterId),
            ],
            'filterPrivacyType' => ['required'],
            'filterType' => ['required']
        ];
    }
}
