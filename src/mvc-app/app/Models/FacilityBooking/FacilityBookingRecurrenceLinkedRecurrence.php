<?php

namespace App\Models\FacilityBooking;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacilityBookingRecurrenceLinkedRecurrence extends Model
{
    use HasFactory;

    /**
     * Const link type moved
     */
    const LINK_TYPE_MOVED = 'moved';

    /**
     * Const link type copied
     */
    const LINK_TYPE_COPIED = 'copied';


    const CREATED_AT = 'FBRLR_Created_ON';
    const UPDATED_AT = null;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'FacilityBookingRecurrenceLinkedRecurrences';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'FBRLR_FacilityBookingRecurrenceLinkedRecurrenceID';
}
