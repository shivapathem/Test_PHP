<?php

namespace App\Models\Facility;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacilityChangeBookingType extends Model
{
    use HasFactory;

    const CREATED_AT = 'FCBT_CreatedDate';
    const UPDATED_AT = 'FCBT_UpdatedDate';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'FacilityChangeBookingTypes';
    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'FCBT_FacilityChangeBookingTypeID';

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'FCBT_CreatedDate' => 'datetime',
        'FCBT_UpdatedDate' => 'datetime',
        'FCBT_FacilityChangeBookingTypeStartDate' => 'date',
        'FCBT_FacilityChangeBookingTypeEndDate' => 'date'
    ];
}
