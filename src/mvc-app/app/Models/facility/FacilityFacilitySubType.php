<?php

namespace App\Models\Facility;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacilityFacilitySubType extends Model
{
    use HasFactory;

    const CREATED_AT = 'FCST_CreatedDate';
    const UPDATED_AT = 'FCST_UpdatedDate';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'FacilityFacilitySubTypes';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'FCST_FacilityID',
        'FCST_FacilitySubTypeID',
        'FCST_PrimarySubType'
    ];

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'FCST_FacilityFacilitySubTypeID';

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'FCST_CreatedDate' => 'datetime',
        'FCST_UpdatedDate' => 'datetime'
    ];
}
