<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class History extends Model
{
    protected $table = 'History';

    protected $primaryKey = 'HistoryID';

    public $timestamps = false;

    // Get history records by type
    public function scopeByHistoryType($query, $historyType)
    {
        return $query->join('HistoryTypes as HY', 'HY.ID', '=', 'History.HistoryType')
                     ->where('HY.HistoryType', '=', $historyType);
    }
}
