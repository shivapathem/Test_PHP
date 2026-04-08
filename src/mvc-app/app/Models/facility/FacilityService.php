<?php

namespace App\Models\Facility;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacilityService extends Model
{
    use HasFactory;

    const CREATED_AT = 'FCSR_CreatedDate';
    const UPDATED_AT = 'FCSR_UpdatedDate';

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
        'FCSR_ServiceID'
    ];

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'FCSR_FacilityServiceID';

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'FCSR_CreatedDate' => 'datetime',
        'FCSR_UpdatedDate' => 'datetime'
    ];
}
