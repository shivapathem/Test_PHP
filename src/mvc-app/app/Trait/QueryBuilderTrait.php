<?php

namespace App\Trait;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

trait QueryBuilderTrait
{
    /**
     * Scope a query to get all linked bookings.
     *
     * @param Builder $query
     */
    public function scopeLinkedBooking(Builder $query): Builder
    {
        return $query->join('FacilityBookingLinkedFacilityBookings as linkedBookingLinks', 'linkedBookingLinks.FBLFB_FacilityBookingID', '=', 'FacilityBookings.FB_FacilityBookingID')
            ->join('Facilities as fctly', 'fctly.FC_FacilityID', '=', 'FacilityBookings.FB_FacilityID')
            ->join('FacilityBookings as linkedBookings', 'linkedBookings.FB_FacilityBookingID', '=', 'linkedBookingLinks.FBLFB_LinkedFacilityBookingID')
            ->join('Facilities as linkedFctly', 'linkedFctly.FC_FacilityID', '=', 'linkedBookings.FB_FacilityID')
            ->leftJoin('FacilityLinks', function ($join) {
                $join->on('FacilityLinks.FCLK_FacilityID', '=', 'fctly.FC_FacilityID');
                $join->where('FacilityLinks.FCLK_LinkedFacilityID', '=', DB::raw('linkedFctly.FC_FacilityID'));
            })->whereNotNull('linkedBookings.FB_FacilityBookingID')
            ->whereNull('linkedBookings.deleted_at')
            ->select('linkedBookings.*', 'FacilityLinks.FCLK_Mandatory');
    }
}
