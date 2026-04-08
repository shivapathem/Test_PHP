<?php

namespace App\Models\Facility;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FacilityType extends Model
{
    use HasFactory;
    use SoftDeletes;

    const CREATED_AT = 'FT_CreatedDate';
    const UPDATED_AT = 'FT_UpdatedDate';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'FacilityTypes';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'FT_FacilityTypeID';

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'FT_CreatedDate' => 'datetime',
        'FT_UpdatedDate' => 'datetime'
    ];

    /**
     * Get the user created the record.
     */
    public function createdBy()
    {
        return $this->hasOne(User::class, 'UD_UserID', 'FT_CreatedBy');
    }

    /**
     * Get the user updated the record.
     */
    public function updatedBy()
    {
        return $this->hasOne(User::class, 'UD_UserID', 'FT_UpdatedBy');
    }

    /**
     * The facility sub type that belong to the facility type.
     */
    public function facilitySubType()
    {
        return $this->hasMany(FacilitySubType::class, 'FST_FacilityTypeID');
    }

    public function facilities()
    {
        return $this->hasMany(
            Facility::class,
            'FC_FacilityTypeID',
            'FT_FacilityTypeID'
        );
    }
}
