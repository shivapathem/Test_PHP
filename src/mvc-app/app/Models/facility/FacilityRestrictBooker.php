<?php

namespace App\Models\Facility;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacilityRestrictBooker extends Model
{
    use HasFactory;

    const CREATED_AT = 'FCRB_CreatedDate';
    const UPDATED_AT = 'FCRB_UpdatedDate';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'FacilityRestrictBookers';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'FCRB_SchedulingTeamID'
    ];

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'FCRB_FacilityRestrictBookerID';

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'FCRB_CreatedDate' => 'datetime',
        'FCRB_UpdatedDate' => 'datetime'
    ];
}
