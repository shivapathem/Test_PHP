<?php

namespace App\Models\Facility;

use App\Models\FacilityBooking\FacilityBooking;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Action extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'Actions';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'action_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'action_name',
        'description',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get facility bookings that have this action.
     */
    public function facilityBookings()
    {
        return $this->belongsToMany(
            FacilityBooking::class,
            'FacilityBookingActions',
            'FBA_ActionID',
            'FBA_FacilityBookingID'
        )->withPivot(['FBA_ActionStartTime', 'FBA_ActionEndTime']);
    }
}
