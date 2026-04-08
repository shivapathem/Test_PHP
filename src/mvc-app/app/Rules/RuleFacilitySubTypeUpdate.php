<?php

namespace App\Rules;

use App\Models\Facility\FacilityType;
use Illuminate\Contracts\Validation\Rule;

class RuleFacilitySubTypeUpdate implements Rule
{
    /*
    * @FacilityType $facilityType
    */
    private $facilityType;

    /*
    * @array $errorMessages
    */
    private $errorMessages = [];

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
        $existingFacilitySubType = $this->facilityType->facilitySubType;
        $existingFacilitySubTypeNotFound = $existingFacilitySubType->whereNotIn('FST_FacilitySubType', $value);
        foreach ($existingFacilitySubTypeNotFound as $existingFacilitySubTypeRemoved) {
            if ($existingFacilitySubTypeRemoved->facilities->count() > 0) {
                $this->errorMessages[$existingFacilitySubTypeRemoved->FST_FacilitySubType] = $existingFacilitySubTypeRemoved->facilities->pluck('FC_FacilityName')->toArray();
            }
        }
        return count($this->errorMessages) == 0;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        $messages = [];
        foreach ($this->errorMessages as $facilitySubType => $facilities) {
            $messages[] = $facilitySubType . ' is mapped to facilities - ' . implode(', ', $facilities);
        }
        return implode(' | ', $messages);
    }
}
