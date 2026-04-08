<?php

namespace App\Models\Facility;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Location extends Model
{
    use HasFactory;
    use SoftDeletes;

    const CREATED_AT = 'LN_CreatedDate';
    const UPDATED_AT = 'LN_UpdatedDate';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'Locations';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'LN_LocationID';

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'LN_CreatedDate' => 'datetime',
        'LN_UpdatedDate' => 'datetime'
    ];

    /**
     * Get the user created the record.
     */
    public function createdBy()
    {
        return $this->hasOne(User::class, 'UD_UserID', 'LN_CreatedBy');
    }

    /**
     * Get the user updated the record.
     */
    public function updatedBy()
    {
        return $this->hasOne(User::class, 'UD_UserID', 'LN_UpdatedBy');
    }
    /**
     * Get facilites
     */
    public function facilities()
    {
        return $this->belongsToMany(
            Facility::class,
            'FacilityLocations',
            'FCL_InternalLocationID',
            'FCL_FacilityID'
        );
    }
}
