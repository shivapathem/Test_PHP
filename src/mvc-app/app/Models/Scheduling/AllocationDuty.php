<?php

namespace App\Models\Scheduling;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AllocationDuty extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'AllocationsDuties';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'AD_AllocationsDutyID';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Get the allocation that owns this duty.
     */
    public function allocation(): BelongsTo
    {
        return $this->belongsTo(Allocation::class, 'AD_AllocationsID', 'AL_AllocationsID');
    }

    /**
     * Get the scheduled persons for this duty.
     */
    public function scheduledPersons(): HasMany
    {
        return $this->hasMany(AllocationScheduledPerson::class, 'ASP_AllocationsDutyID', 'AD_AllocationsDutyID');
    }
}
