<?php

namespace App\Models\Filter;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Filter extends Model
{
    use HasFactory;

    //Privacy types
    const FILTER_PRIVACY_TYPE_PUBLIC = 'public';
    const FILTER_PRIVACY_TYPE_PRIVATE = 'private';

    //Filter types
    const FILTER_TYPE_FACILITY = 'facility';
    const FILTER_TYPE_FACILITY_BOOKING_VIEW = 'facilityBookingView';

    const CREATED_AT = 'FLR_CreatedDate';
    const UPDATED_AT = 'FLR_UpdatedDate';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'Filters';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'FLR_FilterID';

    protected $fillable = ['FLR_FilterName', 'FLR_FilterType', 'FLR_FilterPrivacyType', 'FLR_FilterData_JSON', 'FLR_CreatedBy', 'FLR_UpdatedBy',];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'FLR_CreatedDate' => 'datetime',
        'FLR_UpdatedDate' => 'datetime',
        'array' => 'FLR_FilterData_JSON'
    ];

    /**
     * Get the user created the record.
     */
    public function createdBy()
    {
        return $this->hasOne(User::class, 'UD_UserID', 'FLR_CreatedBy');
    }

    /**
     * Get the user updated the record.
     */
    public function updatedBy()
    {
        return $this->hasOne(User::class, 'UD_UserID', 'FLR_UpdatedBy');
    }

    /**
     * Filter privacy type
     *
     */
    static function filterPrivacyType(): array
    {
        return [
            Filter::FILTER_PRIVACY_TYPE_PUBLIC => 'Public',
            Filter::FILTER_PRIVACY_TYPE_PRIVATE => 'Private'
        ];
    }
}
