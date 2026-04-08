<?php

namespace App\Models\Facility;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FacilitySubType extends Model
{
    use HasFactory;

    const CREATED_AT = 'FST_CreatedDate';
    const UPDATED_AT = 'FST_UpdatedDate';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'FacilitySubTypes';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'FST_FacilitySubTypeID';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'FST_FacilitySubType'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'FST_CreatedDate' => 'datetime',
        'FST_UpdatedDate' => 'datetime'
    ];

    /**
     * Get the Facility associated with the subtype.
     */
    public function facilities()
    {
        return $this->belongsToMany(
            Facility::class,
            'FacilityFacilitySubTypes',
            'FCST_FacilitySubTypeID',
            'FCST_FacilityID'
        );
    }
}
