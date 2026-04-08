<?php

namespace App\Rules;

use App\Models\Facility\Facility;
use App\Models\FacilityBooking\FacilityBooking;
use App\Trait\FacilityUnAvailabilityBookingFormDataTrait;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class RuleFacilityAvailability implements ValidationRule
{
    use FacilityUnAvailabilityBookingFormDataTrait;

    /**
     * Facility
     */
    private $facility;

    /**
     * FacilityBooking
     */
    private $facilityBooking;

    /**
     * Facility booking form data
     */
    private $formData;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(Facility $facility, array $formData = [], ?FacilityBooking $facilityBooking = null)
    {
        $this->facility = $facility;
        $this->formData = $formData;
        $this->facilityBooking = $facilityBooking;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($this->formData['confirmAddUnavailableLinked'] == 1) { // Allow add unavailable linked facility
            return;
        }
        $facilityUnavailabilityDetails = $this->getFacilityBookingUnavailabilityDate($this->facility, $this->formData, $this->facilityBooking);
        if (!empty($facilityUnavailabilityDetails)) {
            $fail(json_encode($facilityUnavailabilityDetails));
        }
        return;
    }
}
