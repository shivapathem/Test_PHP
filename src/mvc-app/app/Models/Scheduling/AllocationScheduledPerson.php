<?php

namespace App\Models\Scheduling;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AllocationScheduledPerson extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'AllocationsScheduledPersons';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'ASP_AllocationsSPID';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Get the duty that owns this scheduled person.
     */
    public function duty(): BelongsTo
    {
        return $this->belongsTo(AllocationDuty::class, 'ASP_AllocationsDutyID', 'AD_AllocationsDutyID');
    }
}
