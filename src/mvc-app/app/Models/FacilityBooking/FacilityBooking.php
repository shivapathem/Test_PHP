<?php

namespace App\Models\FacilityBooking;

use App\Models\Facility\Action;
use App\Models\Facility\Facility;
use App\Models\FacilityBooking\ExternalCustomer;
use App\Models\Facility\FacilitySubType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\HistoryLog;
use App\Models\User;
use App\Trait\QueryBuilderTrait;
use Illuminate\Database\Eloquent\SoftDeletes;

class FacilityBooking extends Model
{
    use HasFactory;
    use SoftDeletes;
    use QueryBuilderTrait;

    //Booking Status
    const BOOKING_STATUS_CONFIRMED = 'confirmed';
    const BOOKING_STATUS_PENDING = 'pending';
    const BOOKING_STATUS_DECLINED = 'declined';
    const BOOKING_STATUS_NEW = 'new';
    const BOOKING_STATUS_CANCELLED = 'cancelled';

    const CREATED_AT = 'FB_CreatedDate';
    const UPDATED_AT = 'FB_UpdatedDate';

    // shorting order
    const BOOKING_SORT_ORDER_ASC = 'asc';
    const BOOKING_SORT_ORDER_DESC = 'desc';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'FacilityBookings';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'FB_FacilityBookingID';

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'FB_BookingStartDateTime' => 'datetime:Y-m-d H:i',
        'FB_BookingEndDateTime' => 'datetime:Y-m-d H:i',
        'FB_CreatedDate' => 'datetime',
        'FB_UpdatedDate' => 'datetime'
    ];

    /**
     * Get the linked facility booking.
     */
    public function linkedFacilityBookings()
    {
        return $this->belongsToMany(
            FacilityBooking::class,
            'FacilityBookingLinkedFacilityBookings',
            'FBLFB_FacilityBookingID',
            'FBLFB_LinkedFacilityBookingID'
        );
    }

    /**
     * Get the linked to facility booking.
     */
    public function linkedToFacilityBooking()
    {
        return $this->hasOneThrough(
            FacilityBooking::class,
            FacilityBookingLinkedFacilityBooking::class,
            'FBLFB_LinkedFacilityBookingID',
            'FB_FacilityBookingID',
            null,
            'FBLFB_FacilityBookingID'
        );
    }

    /**
     * Get the booking recurrence actions.
     */
    public function actions()
    {
        return $this->belongsToMany(
            Action::class,
            'FacilityBookingActions',
            'FBA_FacilityBookingID',
            'FBA_ActionID'
        )->withPivot(['FBA_ActionStartTime', 'FBA_ActionEndTime']);
    }

    /**
     * Get the Facility .
     */
    public function facility()
    {
        return $this->hasOne(
            Facility::class,
            'FC_FacilityID',
            'FB_FacilityID'
        );
    }
    /**
     * Get the Facility SubType.
     */
    public function facilitySubTypes()
    {
        return $this->hasOne(
            FacilitySubType::class,
            'FST_FacilitySubTypeID',
            'FB_FacilitySubTypeID'
        );
    }
    /**
     * Get the External Customer.
     */
    public function externalCustomer()
    {
        return $this->hasOne(
            ExternalCustomer::class,
            'EC_ExternalCustomerID',
            'FB_ExternalCustomerID'
        )->withTrashed();
    }

    /**
     * Get the Facility booking recurrence .
     */
    public function facilityBookingRecurrence()
    {
        return $this->hasOne(
            FacilityBookingRecurrence::class,
            'FBR_FacilityBookingRecurrenceID',
            'FB_FacilityBookingRecurrenceID'
        );
    }

    /**
     * Get the user created the record.
     */
    public function createdBy()
    {
        return $this->hasOne(User::class, 'UD_UserID', 'FB_CreatedBy');
    }

    /**
     * Get all history.
     */
    public function history()
    {
        return $this->hasMany(HistoryLog::class, 'HL_AttributeID')->where('HL_TYPE', HistoryLog::FACILITY_BOOKING);
    }

    /**
     * * Human read date format
     */
    public function getDateOnlyStartDateTimeAttribute()
    {
        return $this->FB_BookingStartDateTime->format('d/m/Y');
    }

    /**
     * * Get duration in minutes
     */
    public function getDurationMinutesAttribute()
    {
        return $this->FB_BookingStartDateTime->diffInMinutes($this->FB_BookingEndDateTime);
    }

    /**
     * * Get booking status with date
     */
    public function getFormattedBookingStatusAttribute()
    {
        $bookingStatus = isset($this->copyBookingDate) ? 'Pending' : ($this->FB_BookingStatus ?? 'New');

        $fieldMap = [
            'cancelled' => 'FB_BookingCancelDate',
            'declined'  => 'FB_BookingDeclineDate',
            'confirmed' => 'FB_BookingConfirmDate',
        ];

        $dateField = $fieldMap[strtolower($bookingStatus)] ?? null;
        $bookingDate = $dateField ? ($this->{$dateField} ?? null) : null;

        return ucfirst($bookingStatus) . ($bookingDate ? ' | ' . \Carbon\Carbon::parse($bookingDate)->format('d/m/Y') : '');
    }
}
