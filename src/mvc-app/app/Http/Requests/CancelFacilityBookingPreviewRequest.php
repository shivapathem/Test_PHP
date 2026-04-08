<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CancelFacilityBookingPreviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'bookingId'    => ['required', 'integer'],
            'cancel_value' => ['required', Rule::in([
                'entire_booking',
                'some_booking',
                'recurrence_booking',
                'nonmandatory_booking',
            ])],
        ];
    }
}
