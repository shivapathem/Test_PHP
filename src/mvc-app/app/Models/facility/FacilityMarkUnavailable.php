<?php

namespace App\Models\Facility;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacilityMarkUnavailable extends Model
{
    use HasFactory;

    const CREATED_AT = 'FMU_CreatedDate';
    const UPDATED_AT = 'FMU_UpdatedDate';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'FacilityMarkUnavailable';
    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'FMU_FacilityMarkUnavailableID';

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'FMU_CreatedDate' => 'datetime',
        'FMU_UpdatedDate' => 'datetime',
        'FMU_FacilityMarkUnavailableStartDate' => 'date',
        'FMU_FacilityMarkUnavailableEndDate' => 'date'
    ];
}
