<?php

namespace App\Rules;

use App\Models\Facility\Service;
use Illuminate\Contracts\Validation\Rule;

class RuleDeleteService implements Rule
{
    private $service;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(Service $service)
    {
        $this->service = $service;
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
        return $this->service->facilities->count() == 0;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return $this->service->SR_Service . '(Service) is mapped to facilities - ' . $this->service->facilities->implode('FC_FacilityName', ", ");
    }
}
