<?php

namespace App\Rules;

use App\Models\Customer\Customer;
use Illuminate\Contracts\Validation\Rule;

class RuleDeleteCustomer implements Rule
{
    private $customer;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(Customer $customer)
    {
        $this->customer = $customer;
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
        return 0;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The validation error message.';
    }
}
