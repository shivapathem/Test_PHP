<?php

namespace App\Models\Facility;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FacilityUserRole extends Model
{
    use HasFactory;
    use SoftDeletes;

    const CREATED_AT = 'FUR_CreatedDate';
    const UPDATED_AT = 'FUR_UpdatedDate';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'FacilityUserRoles';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'FUR_FacilityUserRoleID';

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'FUR_CreatedDate' => 'datetime',
        'FUR_UpdatedDate' => 'datetime'
    ];
}
