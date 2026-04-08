<?php

namespace App\Rules;

use App\Models\Facility\Location;
use Illuminate\Contracts\Validation\Rule;

class RuleDeleteLocation implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(Location $location)
    {
        $this->location = $location;
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
        return $this->location->facilities->count() == 0;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return $this->location->LN_Location . '(Location) is mapped to facilities - ' . $this->location->facilities->implode('FC_FacilityName', ", ");
    }
}
