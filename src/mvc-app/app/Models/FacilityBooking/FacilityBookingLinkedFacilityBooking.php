<?php

namespace App\Models\FacilityBooking;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacilityBookingLinkedFacilityBooking extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'FacilityBookingLinkedFacilityBookings';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'FBLFB_FacilityBookingLinkedFacilityBookingID';
}
