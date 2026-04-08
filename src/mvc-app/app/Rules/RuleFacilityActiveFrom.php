<?php

namespace App\Rules;

use App\Models\Facility\Facility;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\Rule;

class RuleFacilityActiveFrom implements Rule
{
    /**
     * Facility facility
     */
    private $facility;

    /**
     * Facility Bookings
     */
    private $facilityBookings;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(Facility $facility)
    {
        $this->facility = $facility;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $activeFrom = Carbon::createFromFormat('d/m/Y', $value);
        $this->facilityBookings = $this->facility->facilityBookings()->whereDate('FB_BookingStartDateTime', '>=', Carbon::now())->whereDate('FB_BookingStartDateTime', '<', $activeFrom)->get();
        return $this->facilityBookings->count() == 0;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Bookings exists on ' . $this->facilityBookings->pluck('date_only_start_date_time')->unique()->implode(', ') . '.';
    }
}
