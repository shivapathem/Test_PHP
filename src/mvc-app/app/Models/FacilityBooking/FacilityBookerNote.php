<?php

namespace App\Models\FacilityBooking;

use App\Models\Facility\Facility;
use App\Models\HistoryLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FacilityBookerNote extends Model
{
    use HasFactory;
    use SoftDeletes;

    const CREATED_AT = 'FBN_CreatedDate';
    const UPDATED_AT = 'FBN_UpdatedDate';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'FacilityBookerNotes';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'FBN_FacilityBookerNoteID';

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'FBN_StartDateTime' => 'datetime:Y-m-d H:i',
        'FBN_EndDateTime' => 'datetime:Y-m-d H:i',
        'FBN_CreatedDate' => 'datetime',
        'FBN_UpdatedDate' => 'datetime'
    ];

    /**
     * Get the Facility .
     */
    public function facility()
    {
        return $this->hasOne(
            Facility::class,
            'FC_FacilityID',
            'FBN_FacilityID'
        );
    }

    /**
     * Human read date format
     */
    public function getDateOnlyStartDateTimeAttribute()
    {
        return $this->FBN_StartDateTime->format('d/m/Y');
    }

    /**
     * Get the Facility booker note recurrence .
     */
    public function facilityBookerNoteRecurrence()
    {
        return $this->hasOne(
            FacilityBookerNoteRecurrence::class,
            'FBNR_FacilityBookerNoteRecurrenceID',
            'FBN_FacilityBookerNoteRecurrenceID'
        );
    }

    /**
     * Get all history.
     */
    public function history()
    {
        return $this->hasMany(HistoryLog::class, 'HL_AttributeID')->where('HL_TYPE', HistoryLog::FACILITY_BOOKER_NOTE);
    }
}
