<?php

namespace App\Trait;

use Illuminate\Support\Facades\Log;
use Exception;
use App\Events\FacilityUpdated;
use App\Models\Facility\Facility;
use Illuminate\Support\Facades\Auth;

trait WebSocketEventTrait
{
    /**
     * Broadcast to all browser realtime.
     *
     * @param  Facility $facility
     */
    public function triggerFacilityUpdateEvent(Facility $facility)
    {
        try {
            FacilityUpdated::dispatch($facility, Auth::user());
            //For second server queue
            $facilityUpdateEvent = new FacilityUpdated($facility, Auth::user());
            $facilityUpdateEvent->queue = 'websocket2';
            event($facilityUpdateEvent);
        } catch (Exception $e) {
            Log::error($e->getMessage());
        }
    }
}
