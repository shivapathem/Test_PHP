<?php

namespace App\Models\Scheduling;

use Illuminate\Database\Eloquent\Model;

class TimeDimension extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'TimeDimension';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'ID';
}
