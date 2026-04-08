<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistoryLog extends Model
{
    use HasFactory;

    const CREATED_AT = 'HL_Created_ON';
    const UPDATED_AT = null;

    const DATETIME_REPLACE_STRING = ':data_time_replace';

    const FACILITY_HISTORY = 'facility';

    const FACILITY_BOOKING = 'facility_booking';

    const FACILITY_BOOKER_NOTE = 'facility_booker_note';

    // scheduling group history
    const SCHEDULING_GROUP_HISTORY = 'scheduling_group';
    const HISTORY_SORT_ORDER_DESC = 'DESC';
    const HISTORY_SORT_ORDER_ASC = 'ASC';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'HistoryLogs';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'HL_ID';

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'HL_Created_ON' => 'datetime',
        'HL_HLogs' => 'array'
    ];

    /**
     * Get the user created the record.
     */
    public function createdBy()
    {
        return $this->hasOne(User::class, 'UD_UserID', 'HL_Created_BY');
    }

    /**
     * History parent
     */
    public function historyParent()
    {
        return $this->morphTo();
    }
}
