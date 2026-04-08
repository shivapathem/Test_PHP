<?php

namespace App\Http\Requests;

use App\Models\FacilityBooking\FacilityBooking;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class DeleteFacilityBookingRequest extends FormRequest
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
            'facility_booking' => [
                'required',
                function ($attribute, $value, $fail) {
                    $facilityBooking = $this->route('facilityBooking');
                    $query = $facilityBooking->facilityBookingRecurrence->facilityBookings()->where('FB_FacilityID', $facilityBooking->facilityBookingRecurrence->FBR_FacilityID);
                    //On off booking
                    if (!$facilityBooking->facilityBookingRecurrence->is_recurring) {
                        if ($query->whereDate('FB_BookingStartDateTime', '<', Carbon::now())->count() > 0) {
                            $fail('Past One Off Booking cannot be Deleted.');
                        }
                    } else {
                        $query2 = $query->where('FB_BookingStatus', '!=', FacilityBooking::BOOKING_STATUS_NEW);
                        if ($query2->count() > 0) {
                            $fail('There are consumed Bookings in the Series, So Bookings cannot be Deleted.');
                        }
                    }
                }
            ]
        ];
    }
}
