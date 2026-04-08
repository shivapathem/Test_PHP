<?php

namespace App\Models\Scheduling;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Allocation extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'Allocations';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'AL_AllocationsID';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Get the duties for this allocation.
     */
    public function duties(): HasMany
    {
        return $this->hasMany(AllocationDuty::class, 'AD_AllocationsID', 'AL_AllocationsID');
    }
}
