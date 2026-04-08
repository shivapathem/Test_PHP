<?php

namespace App\Models\Facility;

use App\Models\FacilityBooking\FacilityBookerNote;
use App\Models\FacilityBooking\FacilityBooking;
use App\Models\HistoryLog;
use App\Models\Scheduling\Division;
use App\Models\Scheduling\SchedulingTeam;
use App\Models\User;
use App\Trait\QueryBuilderTrait;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Facility extends Model
{
    use HasFactory;
    use SoftDeletes;
    use QueryBuilderTrait;

    const DEFAULT_BOOKING_SELF_BOOKED = 'self_booked';
    const DEFAULT_BOOKING_MANAGED =  'managed';

    const ACCESSIBLE_YES = 'yes';
    const ACCESSIBLE_LIMITED = 'limited';
    const ACCESSIBLE_NO = 'no';

    const BOOKING_PRIVATE_YES = 'yes';
    const BOOKING_PRIVATE_SUMMARY = 'summary';
    const BOOKING_PRIVATE_NO = 'no';

    const CREATED_AT = 'FC_CreatedDate';
    const UPDATED_AT = 'FC_UpdatedDate';

    const ARCHIVED = 'Archived';
    const ACTIVE =  'Active';
    const UNAVAILABLE =  'Unavailable';
    const DELETED =  'Deleted';

    // Facility Roles
    const FACILITY_ROLE_ADMINISTRATOR = 'facility_administrator';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'Facilities';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'FC_FacilityID';

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'FC_ActiveFrom' => 'date',
        'FC_ArchivedDate' => 'date',
        'FC_CreatedDate' => 'datetime',
        'FC_UpdatedDate' => 'datetime'
    ];

    /**
     * Get the user created the record.
     */
    public function createdBy()
    {
        return $this->hasOne(User::class, 'UD_UserID', 'FC_CreatedBy');
    }

    /**
     * Get the user updated the record.
     */
    public function updatedBy()
    {
        return $this->hasOne(User::class, 'UD_UserID', 'FC_UpdatedBy');
    }

    /**
     * Get the list of type booking list
     */
    static function defaultBookingList(): array
    {
        return [
            Facility::DEFAULT_BOOKING_SELF_BOOKED => 'Self Booked',
            Facility::DEFAULT_BOOKING_MANAGED => 'Managed'
        ];
    }

    /**
     * Get the list of privte type list
     */
    static function bookingPrivateList(): array
    {
        return [
            Facility::BOOKING_PRIVATE_NO => 'No',
            Facility::BOOKING_PRIVATE_YES => 'Yes',
            Facility::BOOKING_PRIVATE_SUMMARY => 'Summary'
        ];
    }

    /*
    * Accessible options list
    */
    static function accessibleList(): array
    {
        return [
            Facility::ACCESSIBLE_YES => 'Yes',
            Facility::ACCESSIBLE_LIMITED => 'Limited',
            Facility::ACCESSIBLE_NO => 'No'
        ];
    }

    /**
     * Get the Facility area owner.
     */
    public function facilityAreaOwner()
    {
        return $this->hasOne(
            Division::class,
            'DivisionID',
            'FC_AreaOwnerID'
        );
    }

    /**
     * Get the Facility restrict bookers.
     */
    public function facilityRestrictBookers()
    {
        return $this->belongsToMany(
            SchedulingTeam::class,
            'FacilityRestrictBookers',
            'FCRB_FacilityID',
            'FCRB_SchedulingTeamID'
        );
    }

    /**
     * Get the Facility administrators.
     */
    public function facilityAdministrators()
    {
        return $this->belongsToMany(
            User::class,
            'FacilityUserRoles',
            'FUR_FacilityID',
            'FUR_UserID'
        )->where('FUR_Role', Facility::FACILITY_ROLE_ADMINISTRATOR);
    }

    /**
     * Get the Facility availability.
     */
    public function facilityAvailability()
    {
        return $this->hasOne(
            FacilityAvailability::class,
            'FCA_FacilityID'
        );
    }

    /**
     * Get the Facility Type.
     */
    public function facilityType()
    {
        return $this->hasOne(
            FacilityType::class,
            'FT_FacilityTypeID',
            'FC_FacilityTypeID'
        );
    }

    /**
     * Get the Facility sub types.
     */
    public function facilitySubTypes()
    {
        return $this->belongsToMany(
            FacilitySubType::class,
            'FacilityFacilitySubTypes',
            'FCST_FacilityID',
            'FCST_FacilitySubTypeID'
        )->withPivot('FCST_PrimarySubType');
    }

    /**
     * Get the Facility services.
     */
    public function facilityServices()
    {
        return $this->belongsToMany(
            Service::class,
            'FacilityServices',
            'FCSR_FacilityID',
            'FCSR_ServiceID'
        );
    }

    /**
     * Get the Facility equipments.
     */
    public function facilityEquipments()
    {
        return $this->belongsToMany(
            Equipment::class,
            'FacilityEquipments',
            'FCEQ_FacilityID',
            'FCEQ_EquipmentID'
        )->withPivot(['FCEQ_Quantity', 'FCEQ_Note']);
    }

    /**
     * Get the linked facilities.
     */
    public function linkedFacilities()
    {
        return $this->belongsToMany(
            Facility::class,
            'FacilityLinks',
            'FCLK_FacilityID',
            'FCLK_LinkedFacilityID'
        )->withPivot(['FCLK_Mandatory']);
    }

    /**
     * Get the facilities that it belong to as mandatory.
     */
    public function belongsToLinkedFacilitiesAsMandatory()
    {
        return $this->belongsToMany(
            Facility::class,
            'FacilityLinks',
            'FCLK_LinkedFacilityID',
            'FCLK_FacilityID',
        )->where('FCLK_Mandatory', 1)->withPivot(['FCLK_Mandatory']);
    }

    /**
     * Get the facilities that it belong to as Non mandatory.
     */
    public function belongsToLinkedFacilitiesAsNonMandatory()
    {
        return $this->belongsToMany(
            Facility::class,
            'FacilityLinks',
            'FCLK_LinkedFacilityID',
            'FCLK_FacilityID',
        )->where('FCLK_Mandatory', 0)->withPivot(['FCLK_Mandatory']);
    }

    /**
     * Get the location.
     */
    public function location()
    {
        return $this->hasOne(
            FacilityLocation::class,
            'FCL_FacilityID',
            'FC_FacilityID'
        );
    }

    /**
     * Get the internal location.
     */
    public function internalLocation()
    {
        return $this->hasOneThrough(
            Location::class,
            FacilityLocation::class,
            'FCL_FacilityID',
            'LN_LocationID',
            'FC_FacilityID',
            'FCL_InternalLocationID'
        )->whereNotNull('FCL_InternalLocationID');
    }

    /**
     * Get the Facility change booking type.
     */
    public function facilityChangeBookingType()
    {
        return $this->hasOne(
            FacilityChangeBookingType::class,
            'FCBT_FacilityID'
        );
    }

    /**
     * Get the Facility mark unavailable.
     */
    public function facilityMarkAsUnavailable()
    {
        return $this->hasOne(
            FacilityMarkUnavailable::class,
            'FMU_FacilityID'
        );
    }

    /**
     * Get the provider type human readable.
     *
     * @param  string  $value
     * @return string
     */
    public function getProviderTypeAttribute()
    {
        return FacilityLocation::providerTypeList()[$this->FC_ProviderType];
    }

    /**
     * Get the default booking type human readable.
     *
     * @param  string  $value
     * @return string
     */
    public function getDefaultBookingTypeAttribute()
    {
        return Facility::defaultBookingList()[$this->FC_DefaultBookingType];
    }

    /**
     * Get all history.
     */
    public function history()
    {
        return $this->hasMany(HistoryLog::class, 'HL_AttributeID')->where('HL_TYPE', HistoryLog::FACILITY_HISTORY);
    }

    /**
     * Get all booker notes of facility.
     */
    public function facilityBookerNotes()
    {
        return $this->hasMany(FacilityBookerNote::class, 'FBN_FacilityID');
    }

    /**
     * Get the Facility bookings.
     */
    public function facilityBookings()
    {
        return $this->hasMany(
            FacilityBooking::class,
            'FB_FacilityID',
            'FC_FacilityID'
        );
    }

    /**
     * Read location based on provider type
     */
    public function getCurrentLocationAttribute(): string
    {
        $location = '';
        switch ($this->FC_ProviderType) {
            case FacilityLocation::PROVIDER_TYPE_INTERNAL:
                $location = $this->internalLocation->LN_Location;
                break;
            case FacilityLocation::PROVIDER_TYPE_EXTERNAL_UK:
                $location = implode(', ', $this->location->only(
                    'FCL_BuildingNumberName_EXTUK',
                    'FCL_Street_EXTUK',
                    'FCL_City_EXTUK',
                    'FCL_County_EXTUK',
                    'FCL_Postcode_EXTUK',
                    'FCL_Country_EXTUK'
                ));
                break;
            case FacilityLocation::PROVIDER_TYPE_EXTERNAL_INTERNATIONAL:
                $location = $this->location->FCL_CompleteAddress_EXTINT;
                break;
        }
        return $location;
    }

    /**
     * Read facility current status
     */
    public function getCurrentStatusAttribute(): string
    {
        $status = 'Unavailable';
        $today = Carbon::now();
        if ($this->deleted_at != null) {
            return 'Deleted';
        }
        if ($this->FC_ArchivedDate != null && $this->FC_ArchivedDate->lte($today)) {
            return 'Archived';
        }
        $day = $today->format('l');
        $currentTime = $today->secondsSinceMidnight();
        //Marked unavailable
        if (
            $this->facilityMarkAsUnavailable != null
            && $today->greaterThanOrEqualTo($this->facilityMarkAsUnavailable->FMU_FacilityMarkUnavailableStartDate)
            && ($this->facilityMarkAsUnavailable->FMU_FacilityMarkUnavailableEndDate == null || $today->lessThanOrEqualTo($this->facilityMarkAsUnavailable->FMU_FacilityMarkUnavailableEndDate))
        ) {
            $from = Carbon::parse($this->facilityMarkAsUnavailable->getAttribute('FMU_FacilityMarkUnavailableTimeFrom_' . $day))->secondsSinceMidnight();
            $to = Carbon::parse($this->facilityMarkAsUnavailable->getAttribute('FMU_FacilityMarkUnavailableTimeTo_' . $day))->secondsSinceMidnight();
            if (($from == 0 && $to == 0) || ($currentTime >= $from && $currentTime <= $currentTime)) {
                return 'Unavailable';
            }
        }
        //Availability
        if (
            $today->greaterThanOrEqualTo($this->FC_ActiveFrom)
        ) {
            if ($this->facilityAvailability == null) {
                return 'Active';
            }
            $from = Carbon::parse($this->facilityAvailability->getAttribute('FCA_FacilityTimeFrom_' . $day))->secondsSinceMidnight();
            $to = Carbon::parse($this->facilityAvailability->getAttribute('FCA_FacilityTimeTo_' . $day))->secondsSinceMidnight();

            if (($from == 0 && $to == 0) || ($currentTime >= $from && $currentTime <= $currentTime)) {
                return 'Active';
            }
        }
        return $status;
    }

    /**
     * Get unavailable date time
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param bool $includeFacilityActiveFrom
     *
     * @return array
     */
    public function getUnavailableDateTime(Carbon $startDate, Carbon $endDate, bool $includeFacilityActiveFrom = false): array
    {
        $unAvailable = [];
        foreach (CarbonPeriod::create($startDate, $endDate) as $date) {
            $day = $date->format('l');

            //Facility Active
            if ($includeFacilityActiveFrom && $this->FC_ActiveFrom->gt($date)) {
                $unAvailable[] = [
                    'from' => $date->format('Y-m-d') . ' 00:00',
                    'to'   => $date->copy()->addDays(1)->format('Y-m-d') . ' 00:00',
                    'archived' => 0,
                    'facilityActive' => 0
                ];
                continue;
            }
            //Archived
            if ($this->FC_ArchivedDate != null && $this->FC_ArchivedDate->lte($date)) {
                $unAvailable[] = [
                    'from' => $date->format('Y-m-d') . ' 00:00',
                    'to'   => $date->copy()->addDays(1)->format('Y-m-d') . ' 00:00',
                    'archived' => 1,
                    'facilityActive' => 1
                ];
                continue;
            }
            if ($this->facilityAvailability != null) { //Availability time
                $availability = $this->facilityAvailability->getAttribute('FCA_FacilityTimeAvailability_' . $day);
                $fromTime = $this->facilityAvailability->getAttribute('FCA_FacilityTimeFrom_' . $day);
                $toTime = $this->facilityAvailability->getAttribute('FCA_FacilityTimeTo_' . $day);

                if ($availability == 0) {
                    $unAvailable[] = [
                        'from' => $date->format('Y-m-d') . ' 00:00',
                        'to'   => $date->copy()->addDays(1)->format('Y-m-d') . ' 00:00',
                        'archived' => 0,
                        'facilityActive' => 1
                    ];
                    continue;
                }

                $from = Carbon::parse($date->format('Y-m-d') . ' ' . $fromTime);
                $to = Carbon::parse($date->format('Y-m-d') . ' ' . $toTime);

                // Add unavailable slot before available time
                if ($from->format('H:i') !== '00:00') {
                    $unAvailable[] = [
                        'from' => $from->copy()->startOfDay()->format('Y-m-d H:i'),
                        'to'   => $from->format('Y-m-d H:i'),
                        'archived' => 0,
                        'facilityActive' => 1
                    ];
                }

                // Add unavailable slot after available time
                if ($to->format('H:i') !== '00:00' && $to->gt($from)) {
                    $unAvailable[] = [
                        'from' => $to->format('Y-m-d H:i'),
                        'to'   => $to->copy()->addDays(1)->startOfDay()->format('Y-m-d H:i'),
                        'archived' => 0,
                        'facilityActive' => 1
                    ];
                }
            }

            if ($this->facilityMarkAsUnavailable != null) { //Based on Unavailability
                $fromTime = $this->facilityMarkAsUnavailable->getAttribute('FMU_FacilityMarkUnavailableTimeFrom_' . $day);
                $toTime = $this->facilityMarkAsUnavailable->getAttribute('FMU_FacilityMarkUnavailableTimeTo_' . $day);

                if ($this->facilityMarkAsUnavailable->getAttribute('FMU_FacilityMarkUnavailableIsChecked_' . $day) == 1) {
                    $from = Carbon::parse($date->format('Y-m-d') . ' ' . $fromTime);
                    $to = Carbon::parse($date->format('Y-m-d') . ' ' . $toTime);
                    if ($to->format('H:i') == '00:00') {
                        $to = $to->copy()->addDays(1)->startOfDay();
                    }
                    if (
                        $date->gte($this->facilityMarkAsUnavailable->FMU_FacilityMarkUnavailableStartDate)
                        &&
                        $date->lte($this->facilityMarkAsUnavailable->FMU_FacilityMarkUnavailableEndDate)
                    ) {
                        $unAvailable[] = [
                            'from' => $from->format('Y-m-d H:i'),
                            'to'   => $to->format('Y-m-d H:i'),
                            'archived' => 0,
                            'facilityActive' => 1
                        ];
                    }
                }
            }
        }
        return $unAvailable;
    }

    /**
     * Get the list of status
     */
    static function getStatus(): array
    {
        return [
            Facility::ACTIVE => 'Active',
            Facility::ARCHIVED => 'Archived',
            Facility::UNAVAILABLE => 'Unavailable'
        ];
    }

    /**
     * Get the Facility Contacts emails for the facility as an array.
     *
     * @return string[] Array of email addresses, empty if `FC_Contact` is null or empty.
     */
    public function getFacilityContactEmailsAttribute()
    {
        if (!$this->FC_Contact) {
            return [];
        }
        return array_filter(array_map('trim', explode(',', $this->FC_Contact)));
    }
}
