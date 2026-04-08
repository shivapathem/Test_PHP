<?php

namespace App\Models\User;

use App\Models\Scheduling\SchedulingTeam;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class ScheduledPersonTeamLink extends Model
{
    use HasFactory;

    /**
     * Home team
     */
    const HOME_TEAM = 1;

    /**
     * Additional team
     */
    const ADDITIONAL_TEAM = 0;

    /**
     * Future Home Additional team
     */
    const FUTURE_HOME_ADDITIONAL_TEAM = 2;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ScheduledPersonTeam_LINK';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'SPTeamID';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'ScheduledPersonID',
        'TeamID',
        'IsHomeTeam',
        'StartDate',
        'EndDate',
        'SortCode',
        'BackgroundColour',
        'fontcolour',
        'IsAvailable',
        'IsDefaultBGColour',
        'IsActive',
        'scheduledType',
        'CreatedBy',
        'CreatedDate',
        'LastUpdatedBy',
        'LastUpdatedDate',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'IsAvailable'       => 'boolean',
        'IsDefaultBGColour' => 'boolean',
        'IsActive'          => 'boolean',
    ];

    /**
     * Get the scheduling team associated with the scheduled person team link.
     */
    public function schedulingTeam()
    {
        return $this->hasOne(SchedulingTeam::class, 'schedulingTeamId', 'TeamID');
    }

    /**
     * User Roles associated with the scheduled person team link.
     */
    public function userRoles()
    {
        return $this->hasMany(UserRole::class, 'UR_UserID', 'ScheduledPersonID')
            ->where('UR_EndDate', '>', Carbon::now());
    }

    /**
     * Get the scheduling team associated with this link.
     */
    public function team()
    {
        return $this->belongsTo(\App\Models\Scheduling\SchedulingTeam::class, 'TeamID', 'schedulingTeamId')
                ->withDefault(['schedulingTeamName' => '']);
    }

    /**
     * Get the user who last updated this record.
     */
    public function lastUpdatedByUser()
    {
        return $this->belongsTo(\App\Models\User::class, 'LastUpdatedBy', 'UD_UserID');
    }

    /**
     * Get the user who created this record.
     */
    public function createdByUser()
    {
        return $this->belongsTo(\App\Models\User::class, 'CreatedBy', 'UD_UserID');
    }

    /**
     * Get the scheduled person (user) associated with this link.
     */
    public function scheduledPerson()
    {
        return $this->hasOne(User::class, 'UD_UserID', 'ScheduledPersonID');
    }
}
