<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;

class RolePermissionStatus extends Model
{
    public $timestamps = false;
    protected $table = 'REF_RolePermissionStatus';
    protected $primaryKey = 'ID';

    protected $fillable = [
        'MainRoleID',
        'AdditionalRoleID',
        'PermissionKey',
        'PermissionDescription',
        'IsActive'
    ];

    protected $casts = [
        'IsActive' => 'boolean',
        'CreatedDate' => 'datetime'
    ];

    // Permission Status Constants
    const MANDATORY = 'MANDATORY';
    const OPTIONAL = 'OPTIONAL';
    const CONDITIONAL = 'CONDITIONAL';
    const TICK_CONDITIONAL = 'TICK_CONDITIONAL';
    const NA = 'NA';

    public function mainRole()
    {
        return $this->belongsTo(RefRole::class, 'MainRoleID', 'RoleID');
    }

    public function additionalRole()
    {
        return $this->belongsTo(RefRole::class, 'AdditionalRoleID', 'RoleID');
    }
}
