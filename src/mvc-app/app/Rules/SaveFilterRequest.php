<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use App\Models\Filter\Filter;
use Illuminate\Support\Facades\Auth;

class SaveFilterRequest implements ValidationRule
{
    private $filterType;
    private $filterPrivacyType;
    private $filterId;

    /**
     * Create a new rule instance.
     *
     * @param string $filterType
     * @param string $filterPrivacyType
     */
    public function __construct($filterType, $filterPrivacyType, $filterId)
    {
        $this->filterType = $filterType;
        $this->filterPrivacyType = $filterPrivacyType;
        $this->filterId = $filterId;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $query = Filter::where('FLR_FilterName', $value)
            ->where('FLR_FilterType', $this->filterType)
            ->where('FLR_FilterPrivacyType', $this->filterPrivacyType);

        if ($this->filterPrivacyType === Filter::FILTER_PRIVACY_TYPE_PRIVATE) {
            $query->where('FLR_CreatedBy', Auth::id());
        }

        $filter = $query->first();

        // PUBLIC FILTER VALIDATION
        if ($this->filterPrivacyType === Filter::FILTER_PRIVACY_TYPE_PUBLIC) {
            if (!Auth::user()->isFacilityAdministrator) {
                $fail('You are not an admin.');
            }

            if (empty($this->filterId) && $filter) {
                $fail('A filter with this name already exists.');
            }
        }

        // PRIVATE FILTER VALIDATION
        if ($this->filterPrivacyType === Filter::FILTER_PRIVACY_TYPE_PRIVATE) {
            if (empty($this->filterId) && $filter && $filter->FLR_CreatedBy == Auth::id()) {
                $fail('A filter with this name already exists.');
            }
        }
    }
}
