<?php

namespace App\Events;

use App\Models\Facility\Facility;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FacilityUpdated implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /**
     * Facility $facility
     */
    public $facility;

     /**
     * User $user
     */
    public $user;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Facility $facility, User $user)
    {
        $this->facility = $facility;
        $this->user = $user;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('facility-update');
    }

    /**
     * Broadcasting details
     */
    public function broadcastWith()
    {
        return [
            'facilityId' => $this->facility->FC_FacilityID,
            'actionPerformedBy' => [
                'userId' => $this->user->UD_UserID
            ]
        ];
    }
}
