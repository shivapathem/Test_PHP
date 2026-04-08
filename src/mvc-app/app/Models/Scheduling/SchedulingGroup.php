<?php

namespace App\Models\Scheduling;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Scheduling\Division;
use App\Models\HistoryLog;
use App\Models\Scheduling\SchedulingTeam;

class SchedulingGroup extends Model
{
    use HasFactory;
    use SoftDeletes;

    const CREATED_AT = 'CreatedDate';
    const UPDATED_AT = 'UpdatedDate';
    const DELETED_AT = 'DeletedAt';
    // sorting order
    const SORT_ORDER_DESC = 'DESC';
    const SORT_ORDER_ASC = 'ASC';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'SchedulingGroups';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'SchedulingGroupsID';

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'CreatedDate' => 'datetime',
        'UpdatedDate' => 'datetime',
        'IsIncludeINMenu' => 'boolean'
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'SchedulingGroupsName',
        'DivisionID',
        'SchedulingTeamID',
        'IsIncludeINMenu',
        'CreatedBy',
        'UpdatedBy',
        'Notes'
    ];

    /**
     * Get the area (division) that owns the scheduling group.
     */
    public function area()
    {
        return $this->belongsTo(Division::class, 'DivisionID', 'DivisionID');
    }

    /**
     * The scheduling teams that belong to the scheduling group.
     */
    public function schedulingTeams()
    {
        return $this->belongsToMany(SchedulingTeam::class, 'SchedulingGroupsTeamsLinks', 'SchedulingGroupsID', 'SchedulingTeamID')
            ->withPivot(['EndDate'])
            ->where(function ($team) {
                $team->where('SchedulingGroupsTeamsLinks.EndDate', '9999-01-01')
                    ->orWhere('SchedulingGroupsTeamsLinks.EndDate', '>', now()->toDateString());
            });
    }

    /**
     * Get the user who created the scheduling group.
     */
    public function createdBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'CreatedBy', 'UD_UserID');
    }

    /**
     * Get the user who last updated the scheduling group.
     */
    public function updatedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'UpdatedBy', 'UD_UserID');
    }

    /**
     * Get all history.
     */
    public function history()
    {
        return $this->hasMany(HistoryLog::class, 'HL_AttributeID')->where('HL_TYPE', HistoryLog::SCHEDULING_GROUP_HISTORY);
    }
}
