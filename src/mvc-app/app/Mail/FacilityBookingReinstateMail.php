<?php

namespace App\Mail;

use App\Models\FacilityBooking\FacilityBooking;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class FacilityBookingReinstateMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    /**
     * @var User $user
     */
    public $user;

    /**
     * @var FacilityBooking $facilityBooking
     */

    public $facilityBooking;

    /**
     * @var Collection $facilityBookings
     */

    public $facilityBookings;

    public function __construct(Collection $facilityBookings)
    {
        $this->facilityBookings = $facilityBookings;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $this->facilityBooking = $this->facilityBookings->first();
        $this->user = $this->facilityBooking->createdBy;
        return $this->view('mails.FacilityBooking.reinstate-facility-booking');
    }
}
