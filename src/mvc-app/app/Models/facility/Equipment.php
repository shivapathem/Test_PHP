<?php

namespace App\Models\Facility;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Equipment extends Model
{
    use HasFactory;
    use SoftDeletes;

    const EQUIPMENT_TYPE_SW =  'SW';
    const EQUIPMENT_TYPE_ET =  'ET';
    const CREATED_AT = 'EQ_CreatedDate';
    const UPDATED_AT = 'EQ_UpdatedDate';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'Equipment';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'EQ_EquipmentID';

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'EQ_CreatedDate' => 'datetime',
        'EQ_UpdatedDate' => 'datetime'
    ];

    /**
     * Get the user created the record.
     */
    public function createdBy()
    {
        return $this->hasOne(User::class, 'UD_UserID', 'EQ_CreatedBy');
    }

    /**
     * Get the user updated the record.
     */
    public function updatedBy()
    {
        return $this->hasOne(User::class, 'UD_UserID', 'EQ_UpdatedBy');
    }

    /**
     * checking Equipment mapped with the facility
     */

    public function facilities()
    {
        return $this->belongsToMany(
            Facility::class,
            'FacilityEquipments',
            'FCEQ_EquipmentID',
            'FCEQ_FacilityID'
        );
    }
    static function EquipemntTypeList(): array
    {
        return [
            Equipment::EQUIPMENT_TYPE_SW => 'Software',
            Equipment::EQUIPMENT_TYPE_ET => 'Equipment'
        ];
    }
    /**
     * Get the string as human readable.
     *
     * @return string
     */
    public function getFacilityEquipmentDetailAttribute()
    {
        return $this->EQ_Equipment . ' (Quantity - ' . $this->getOriginal('pivot_FCEQ_Quantity') . ' Note - ' . $this->getOriginal('pivot_FCEQ_Note') . ' )';
    }
}
