<?php

namespace App\Mail;

use App\Models\FacilityBooking\FacilityBooking;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class FacilityBookingCancelled extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    /**
     * @var User $user
     */
    public $user;

    /**
     * @var Collection $cancelledDates
     */
    public $cancelledDates;

    /**
     * @var FacilityBooking $facilityBooking
     */

    public $facilityBooking;

    public function __construct(User $user, FacilityBooking $facilityBooking, Collection $cancelledDates)
    {
        $this->user = $user;
        $this->facilityBooking = $facilityBooking;
        $this->cancelledDates = $cancelledDates;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('mails.FacilityBooking.cancelled-facility-booking');
    }
}
