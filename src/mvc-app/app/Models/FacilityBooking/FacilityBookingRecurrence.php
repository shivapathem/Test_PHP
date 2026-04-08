<?php

namespace App\Models\FacilityBooking;

use App\Models\Facility\Action;
use App\Models\Facility\Facility;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacilityBookingRecurrence extends Model
{
    use HasFactory;

    //Booking type
    const BOOKING_TYPE_REQUEST = 'request';
    const BOOKING_TYPE_RECORD = 'record';

    //Private
    const PRIVATE_BOOKING_YES = 'yes';
    const PRIVATE_BOOKING_NO = 'no';
    const PRIVATE_BOOKING_SUMMARY = 'summary';

    //Customer type
    const CUSTOMER_TYPE_INTERNAL = 'internal';
    const CUSTOMER_TYPE_EXTERNAL = 'external';

    //Recurrence type
    const RECUR_TYPE_WEEKLY = 'weekly';
    const RECUR_TYPE_DAILY = 'daily';

    const CREATED_AT = 'FBR_CreatedDate';
    const UPDATED_AT = 'FBR_UpdatedDate';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'FacilityBookingRecurrence';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'FBR_FacilityBookingRecurrenceID';

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'FBR_SeriesStartDate' => 'date',
        'FBR_SeriesEndDate' => 'date',
        'FBR_CreatedDate' => 'datetime',
        'FBR_UpdatedDate' => 'datetime'
    ];

    /**
     * Private access
     */
    static function privateBookingTypes(): array
    {
        return [
            FacilityBookingRecurrence::PRIVATE_BOOKING_NO => 'No',
            FacilityBookingRecurrence::PRIVATE_BOOKING_YES => 'Yes',
            FacilityBookingRecurrence::PRIVATE_BOOKING_SUMMARY => 'Yes (Show Summary)'
        ];
    }

    /**
     * Customer types
     */
    static function customerTypes(): array
    {
        return [
            FacilityBookingRecurrence::CUSTOMER_TYPE_INTERNAL => 'Internal',
            FacilityBookingRecurrence::CUSTOMER_TYPE_EXTERNAL => 'External'
        ];
    }

    /**
     * Get the linked facilities.
     */
    public function linkedFacilities()
    {
        return $this->belongsToMany(
            Facility::class,
            'FacilityBookingRecurrenceLinkedFacility',
            'FBRLF_FacilityBookingRecurrenceID',
            'FBRLF_FacilityID'
        );
    }

    /**
     * Get the linked facility recurrence.
     */
    public function linkedFacilityBookingRecurrence()
    {
        return $this->belongsToMany(
            FacilityBookingRecurrence::class,
            'FacilityBookingRecurrenceLinkedRecurrences',
            'FBRLR_FacilityBookingRecurrenceID',
            'FBRLR_LinkedFacilityBookingRecurrenceID'
        );
    }

    /**
     * Get the booking recurrence actions.
     */
    public function actions()
    {
        return $this->belongsToMany(
            Action::class,
            'FacilityBookingRecurrenceActions',
            'FBRA_FacilityBookingRecurrenceID',
            'FBRA_ActionID'
        )->withPivot(['FBRA_ActionStartTime', 'FBRA_ActionEndTime']);
    }

    /**
     * Get the Facility .
     */
    public function facility()
    {
        return $this->hasOne(
            Facility::class,
            'FC_FacilityID',
            'FBR_FacilityID'
        );
    }

    /**
     * Get the Facility Bookings
     */
    public function facilityBookings()
    {
        return $this->hasMany(
            FacilityBooking::class,
            'FB_FacilityBookingRecurrenceID',
            'FBR_FacilityBookingRecurrenceID'
        );
    }

    /**
     * Available Facility bookings
     *
     * @param bool $excludeLinkedBookings exclude linked bookings
     * @param bool $excludePastBooking  exclude past bookings
     */
    public function availableFacilityBookings(bool $excludeLinkedBookings = false, bool $excludePastBooking = true)
    {
        $query = $this->facilityBookings();
        if ($excludePastBooking) {
            $query->whereDate('FB_BookingStartDateTime', '>=', Carbon::now());
        }
        if ($excludeLinkedBookings) {
            $query->where('FB_FacilityID', $this->FBR_FacilityID);
        }
        return $query;
    }

    /**
     * Check the Facility Recurrence start date can be edited
     */
    public function facilityRecurrenceStartDateEditable(): bool
    {
        return $this->facilityBookings()
            ->where('FB_BookingStartDateTime', '<', Carbon::now()->startOfDay())
            ->count() > 0 ? false : true;
    }

    /**
     * Check if the recurrence is recurring
     */
    public function getIsRecurringAttribute(): bool
    {
        return $this->FBR_SeriesStartDate->notEqualTo($this->FBR_SeriesEndDate);
    }

    /**
     * Recurrence Detail string
     */
    public function getRecurrenceSettingStringAttribute(): string
    {
        $dayDetails = $this->FBR_RecurrenceType == FacilityBookingRecurrence::RECUR_TYPE_DAILY ? $this->FBR_RecurrenceDayInterval . ' day(s)' : $this->FBR_RecurrenceWeekInterval . ' week(s)';
        if ($this->FBR_RecurrenceType == FacilityBookingRecurrence::RECUR_TYPE_WEEKLY) {
            $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
            $checkedDays = [];
            foreach ($days as $day) {
                if ($this->{'FBR_RecurrenceWeek_' . $day} == 1) {
                    $checkedDays[] = $day;
                }
            }
            $dayDetails = $dayDetails . ' on ' . implode(', ', $checkedDays);
        }
        $recurringSetting = __(
            'Start Date : :startDate End Date : :endDate Start Time : :startTime End Time : :endTime, Recur every :dayDetail',
            [
                'startDate' =>  $this->FBR_SeriesStartDate->format('d/m/Y'),
                'endDate' =>  $this->FBR_SeriesEndDate->format('d/m/Y'),
                'startTime' => substr($this->FBR_StartTime, 0, 5),
                'endTime' => substr($this->FBR_EndTime, 0, 5),
                'dayDetail' => $dayDetails
            ]
        );
        return $recurringSetting;
    }

    /**
     * Get dates for creating bookings
     *
     * @param User $user Based on user access skip unavailability
     */
    public function getAvailableDates(?User $user = null): array
    {
        $availability = $this->facility->facilityAvailability;
        $unAvailability = $this->facility->facilityMarkAsUnavailable;
        $availableDates = [];
        $isBooker = $user != null ? $user->haveAccessToFacility($this->facility) : false;
        foreach (CarbonPeriod::create($this->FBR_SeriesStartDate, $this->FBR_SeriesEndDate) as $date) {
            $day = $date->format('l');

            //Archived
            if ($this->facility->FC_ArchivedDate != null && $this->facility->FC_ArchivedDate->lte($date)) {
                continue;
            }

            // Calculate weeks and days passed
            $daysPassed = $this->FBR_SeriesStartDate->diffInDays($date);

            //Skip based on recurs day or week
            if ($this->FBR_RecurrenceType == FacilityBookingRecurrence::RECUR_TYPE_WEEKLY) {
                if ($this->{'FBR_RecurrenceWeek_' . $day} != 1) {
                    continue;
                }
                if ($this->FBR_RecurrenceWeekInterval > 1) {
                    $weeksPassed = intdiv($daysPassed, 7);
                    if ($weeksPassed % $this->FBR_RecurrenceWeekInterval !== 0) {
                        continue;
                    }
                }
            } elseif ($this->FBR_RecurrenceType == FacilityBookingRecurrence::RECUR_TYPE_DAILY) {
                if ($this->FBR_RecurrenceDayInterval > 1) {
                    if ($daysPassed % $this->FBR_RecurrenceDayInterval !== 0) {
                        continue;
                    }
                }
            }

            // Check facility time availability
            // Booker can book during unavailability
            if ($availability->{'FCA_FacilityTimeAvailability_' . $day} == 0 && !$isBooker) {
                continue;
            }

            // Check mark as unavailable
            // Booker can book during unavailability
            if ($unAvailability != null && !$isBooker) {
                $unStartDate = $unAvailability->FMU_FacilityMarkUnavailableStartDate;
                $unEndDate = $unAvailability->FMU_FacilityMarkUnavailableEndDate;
                $unStartTime = Carbon::createFromFormat('H:i', substr($unAvailability->{'FMU_FacilityMarkUnavailableTimeFrom_' . $day}, 0, 5));
                $unEndTime = Carbon::createFromFormat('H:i', substr($unAvailability->{'FMU_FacilityMarkUnavailableTimeTo_' . $day}, 0, 5));

                if (
                    $date->betweenIncluded($unStartDate, $unEndDate) &&
                    $unStartTime->format('H:i') === '00:00' &&
                    $unEndTime->format('H:i') === '00:00' &&
                    $unAvailability->{'FMU_FacilityMarkUnavailableIsChecked_' . $day} == 1
                ) {
                    continue;
                }
            }
            $availableDates[] = $date;
        }
        return $availableDates;
    }

    /**
     * Check if recurrence setting is updated array
     */
    public function checkRecurrenceDifferenceArray(): array
    {
        return [
            "FBR_SeriesStartDate",
            "FBR_SeriesEndDate",
            "FBR_RecurrenceType",
            "FBR_RecurrenceDayInterval",
            "FBR_RecurrenceWeekInterval",
            "FBR_RecurrenceWeek_Saturday",
            "FBR_RecurrenceWeek_Sunday",
            "FBR_RecurrenceWeek_Monday",
            "FBR_RecurrenceWeek_Tuesday",
            "FBR_RecurrenceWeek_Wednesday",
            "FBR_RecurrenceWeek_Thursday",
            "FBR_RecurrenceWeek_Friday"
        ];
    }
}
