<?php

namespace App\Rules;

use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use App\Models\FacilityBooking\FacilityBookingRecurrence;

/**
 * RuleRecurringDate
 *
 * Validates that a given time (start or end time) is valid
 * based on the FIRST recurring date that falls on or after
 * the selected booking date.
 *
 * This rule is used only for WEEKLY recurring bookings.
 */
class RuleRecurringDate implements ValidationRule
{
    /**
     * @var Carbon  The booking date selected by the user.
     */
    protected Carbon $bookingDate;

    /**
     * @var string  The recurrence type (e.g., 'weekly', 'none').
     */
    protected string $bookingType;

    /**
     * @var array  List of recurring weekdays (e.g., ['monday', 'wednesday']).
     */
    protected array $recurringDays;

    /**
     * @var string  Error message for weekly recurring validation failure.
     */
    protected string $weeklyMessage;

    /**
     * @var string  Error message for non‑recurring (today) validation failure.
     */
    protected string $todayMessage;

    /**
     * @var Carbon  Start time
     */
    protected $startTime;

    /**
     * Constructor receives all required data from the Form Request.
     *
     * @param Carbon $bookingDate
     * @param string $bookingType
     * @param array $recurringDays
     * @param string $weeklyMessage
     * @param string $todayMessage
     */

    public function __construct(Carbon $bookingDate, string $bookingType, array $recurringDays, string $weeklyMessage, string $todayMessage, ?Carbon $startTime = null)
    {
        $this->bookingDate   = $bookingDate;
        $this->bookingType   = $bookingType;
        $this->recurringDays = $recurringDays;
        $this->weeklyMessage = $weeklyMessage;
        $this->todayMessage = $todayMessage;
        $this->startTime = $startTime;
    }
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $valueTime = Carbon::createFromFormat('H:i', $value);

        //Add day for over midnight bookings
        if ($this->startTime != null && $valueTime->lt($this->startTime)) {
            $valueTime->addDays(1);
        }

        // WEEKLY recurring booking logic
        if ($this->bookingType == FacilityBookingRecurrence::RECUR_TYPE_WEEKLY && !empty($this->recurringDays)) {
            $firstOccurrence = $this->getFirstRecurringDate($this->bookingDate, $this->recurringDays);

            $firstOccurrenceDateTime = $firstOccurrence->copy()->setTimeFrom($valueTime);

            if (!$firstOccurrenceDateTime->isFuture()) {
                $fail($this->weeklyMessage);
            }

            return;
        }

        // NON‑RECURRING booking logic
        if ($this->bookingDate->isToday()) {
            if ($valueTime->isPast()) {
                $fail($this->todayMessage);
            }
        }
    }

    /**
     * Determine the first recurring date on or after the booking date.
     *
     * Example:
     *   Booking date: 9 Jan (Friday)
     *   Recurring days: ['friday', 'saturday']
     *   → Returns 9 Jan (Friday)
     *
     *   Booking date: 9 Jan (Friday)
     *   Recurring days: ['monday']
     *   → Returns 12 Jan (Monday)
     *
     * @param Carbon $startDate
     * @param array $days
     * @return Carbon
     */
    private function getFirstRecurringDate(Carbon $startDate, array $days): Carbon
    {
        $normalizedDays = collect($days)
            ->map(fn($d) => strtolower($d))
            ->values()
            ->all();

        $date = $startDate->copy();

        while (true) {
            $dayName = strtolower($date->format('l'));

            if (in_array($dayName, $normalizedDays, true)) {
                return $date;
            }

            $date->addDay();
        }
    }
}
