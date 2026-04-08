<?php

namespace App\Models\FacilityBooking;

use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacilityBookerNoteRecurrence extends Model
{
    use HasFactory;

    const CREATED_AT = 'FBNR_CreatedDate';
    const UPDATED_AT = 'FBNR_UpdatedDate';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'FacilityBookerNoteRecurrence';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'FBNR_FacilityBookerNoteRecurrenceID';

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'FBNR_SeriesStartDate' => 'date',
        'FBNR_SeriesEndDate' => 'date',
        'FBNR_CreatedDate' => 'datetime',
        'FBNR_UpdatedDate' => 'datetime'
    ];

    /**
     * Get the Facility booker notes
     */
    public function facilityBookerNotes()
    {
        return $this->hasMany(
            FacilityBookerNote::class,
            'FBN_FacilityBookerNoteRecurrenceID',
            'FBNR_FacilityBookerNoteRecurrenceID'
        );
    }

    /**
     * Check if the recurrence is recurring
     */
    public function getIsRecurringAttribute(): bool
    {
        return $this->FBNR_SeriesStartDate->notEqualTo($this->FBNR_SeriesEndDate);
    }

    /**
     * Check if recurrence setting is updated array
     */
    public function checkRecurrenceDifferenceArray(): array
    {
        return [
            "FBNR_SeriesStartDate",
            "FBNR_SeriesEndDate",
            "FBNR_RecurrenceType",
            "FBNR_RecurrenceDayInterval",
            "FBNR_RecurrenceWeekInterval",
            "FBNR_RecurrenceWeek_Saturday",
            "FBNR_RecurrenceWeek_Sunday",
            "FBNR_RecurrenceWeek_Monday",
            "FBNR_RecurrenceWeek_Tuesday",
            "FBNR_RecurrenceWeek_Wednesday",
            "FBNR_RecurrenceWeek_Thursday",
            "FBNR_RecurrenceWeek_Friday"
        ];
    }

    /**
     * Get dates for creating bookings
     */
    public function getAvailableDates(): array
    {
        $availableDates = [];
        // Normalize intervals once (not inside the loop)
        $dayInterval  = max(1, (int) $this->FBNR_RecurrenceDayInterval);
        $weekInterval = max(1, (int) $this->FBNR_RecurrenceWeekInterval);

        foreach (
            CarbonPeriod::create(
                $this->FBNR_SeriesStartDate,
                $this->FBNR_SeriesEndDate
            ) as $date
        ) {
            $day = $date->format('l');
            $daysPassed = $this->FBNR_SeriesStartDate->diffInDays($date);
            if ($this->FBNR_RecurrenceType === FacilityBookingRecurrence::RECUR_TYPE_WEEKLY) {
                // Skip if weekday is not selected
                if ($this->{'FBNR_RecurrenceWeek_' . $day} != 1) {
                    continue;
                }
                $weeksPassed = intdiv($daysPassed, 7);
                // Skip if not on the correct week interval
                if ($weeksPassed % $weekInterval !== 0) {
                    continue;
                }
            } elseif ($this->FBNR_RecurrenceType === FacilityBookingRecurrence::RECUR_TYPE_DAILY) {
                // Skip if not on the correct day interval
                if ($daysPassed % $dayInterval !== 0) {
                    continue;
                }
            }
            $availableDates[] = $date;
        }

        return $availableDates;
    }
}
