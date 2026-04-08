<?php

namespace App\Rules;

use App\Models\Facility\Equipment;
use Illuminate\Contracts\Validation\Rule;

class RuleDeleteEquipment implements Rule
{
    private $equipment;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(Equipment $equipment)
    {
        $this->equipment = $equipment;
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
        return $this->equipment->facilities->count() == 0;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return $this->equipment->EQ_Equipment . '(Equipment) is mapped to facilities - ' . $this->equipment->facilities->implode('FC_FacilityName', ", ");
    }
}
