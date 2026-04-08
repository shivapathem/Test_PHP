<?php

namespace App\Models\Leave;

use Illuminate\Database\Eloquent\Model;

class LeaveRequestGroup extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'LeaveRequestGroups';

     /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'ID';
}
