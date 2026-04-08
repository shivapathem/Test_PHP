<?php

namespace App\Models\FacilityBooking;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExternalCustomer extends Model
{
    use HasFactory;
    use SoftDeletes;

    const CREATED_AT = 'EC_CreatedDate';
    const UPDATED_AT = 'EC_UpdatedDate';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ExternalCustomers';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'EC_ExternalCustomerID';

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'EC_CreatedDate' => 'datetime',
        'EC_UpdatedDate' => 'datetime'
    ];

    /**
     * Get location
     */
    function getLocationAttribute(): string
    {
        $location = '';
        if ($this->EC_InternationalAddress != null) {
            $location = $this->EC_InternationalAddress;
        } else {
            $location = implode(', ', [
                $this->EC_BuildingNumber,
                $this->EC_Street,
                $this->EC_City,
                $this->EC_County,
                $this->EC_PostCode,
                $this->EC_Country
            ]);
        }
        return $location;
    }

    /**
     * Get list of external customers for booking
     *
     * @param FacilityBooking $facilityBooking
     */
    public static function getListOfExternalCustomers(?FacilityBooking $facilityBooking = null)
    {
        $externalCustomers = ExternalCustomer::all();
        if ($facilityBooking != null && $facilityBooking->externalCustomer != null) { // Get deleted external customer of a facility booking
            $externalCustomers = $externalCustomers->push($facilityBooking->externalCustomer)->unique('EC_ExternalCustomerID');
        }
        return $externalCustomers;
    }
}
