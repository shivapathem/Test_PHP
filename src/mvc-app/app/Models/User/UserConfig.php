<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;

class UserConfig extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'UserConfigs';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'UC_UserConfigID';
}
