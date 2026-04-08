<?php

namespace App\Models\Facility;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacilityLocation extends Model
{
    use HasFactory;

    const PROVIDER_TYPE_INTERNAL =  'internal';
    const PROVIDER_TYPE_EXTERNAL_UK =  'external_uk';
    const PROVIDER_TYPE_EXTERNAL_INTERNATIONAL =  'external_international';

    const CREATED_AT = 'FCL_CreatedDate';
    const UPDATED_AT = 'FCL_UpdatedDate';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'FacilityLocations';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'FCL_FacilityLocationID';

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'FCL_CreatedDate' => 'datetime',
        'FCL_UpdatedDate' => 'datetime'
    ];

    static function providerTypeList(): array
    {
        return [
            FacilityLocation::PROVIDER_TYPE_INTERNAL => 'Internal',
            FacilityLocation::PROVIDER_TYPE_EXTERNAL_UK => 'External UK',
            FacilityLocation::PROVIDER_TYPE_EXTERNAL_INTERNATIONAL => 'External International'
        ];
    }
}
