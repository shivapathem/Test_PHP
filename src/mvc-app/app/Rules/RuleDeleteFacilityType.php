<?php

namespace App\Rules;

use App\Models\Facility\FacilityType;
use Illuminate\Contracts\Validation\Rule;

class RuleDeleteFacilityType implements Rule
{
    private $facilityType;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(FacilityType $facilityType)
    {
        $this->facilityType = $facilityType;
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
        return $this->facilityType->facilities->count() == 0;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
         return $this->facilityType->FT_FacilityType . '(Facility Type) is mapped to facilities - ' . $this->facilityType->facilities->implode('FC_FacilityName', ", ");
    }
}
