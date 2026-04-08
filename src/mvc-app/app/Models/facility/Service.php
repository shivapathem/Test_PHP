<?php

namespace App\Models\Facility;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use HasFactory;
    use SoftDeletes;

    const CREATED_AT = 'SR_CreatedDate';
    const UPDATED_AT = 'SR_UpdatedDate';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'Services';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'SR_ServiceID';

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'SR_CreatedDate' => 'datetime',
        'SR_UpdatedDate' => 'datetime'
    ];

    /**
     * Get the user created the record.
     */
    public function createdBy()
    {
        return $this->hasOne(User::class, 'UD_UserID', 'SR_CreatedBy');
    }

    /**
     * Get the user updated the record.
     */
    public function updatedBy()
    {
        return $this->hasOne(User::class, 'UD_UserID', 'SR_UpdatedBy');
    }
    /**
     * checking services mapped with the facility
     */
    public function facilities()
    {
        return $this->belongsToMany(
            Facility::class,
            'FacilityServices',
            'FCSR_ServiceID',
            'FCSR_FacilityID'
        );
    }
}
