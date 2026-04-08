<?php

namespace App\Models\Facility;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacilityAvailability extends Model
{
    use HasFactory;

    const CREATED_AT = 'FCA_CreatedDate';
    const UPDATED_AT = 'FCA_UpdatedDate';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'FacilityAvailability';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'FCA_FacilityAvailabilityID',
        'FCA_FacilityID',
        'FCA_FacilityTimeFrom_Saturday',
        'FCA_FacilityTimeTo_Saturday',
        'FCA_FacilityTimeAvailability_Saturday',
        'FCA_FacilityTimeFrom_Sunday',
        'FCA_FacilityTimeTo_Sunday',
        'FCA_FacilityTimeAvailability_Sunday',
        'FCA_FacilityTimeFrom_Monday',
        'FCA_FacilityTimeTo_Monday',
        'FCA_FacilityTimeAvailability_Monday',
        'FCA_FacilityTimeFrom_Tuesday',
        'FCA_FacilityTimeTo_Tuesday',
        'FCA_FacilityTimeAvailability_Tuesday',
        'FCA_FacilityTimeFrom_Wednesday',
        'FCA_FacilityTimeTo_Wednesday',
        'FCA_FacilityTimeAvailability_Wednesday',
        'FCA_FacilityTimeFrom_Thursday',
        'FCA_FacilityTimeTo_Thursday',
        'FCA_FacilityTimeAvailability_Thursday',
        'FCA_FacilityTimeFrom_Friday',
        'FCA_FacilityTimeTo_Friday',
        'FCA_FacilityTimeAvailability_Friday'
    ];

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'FCA_FacilityAvailabilityID';

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'FCA_CreatedDate' => 'datetime',
        'FCA_UpdatedDate' => 'datetime'
    ];
}
