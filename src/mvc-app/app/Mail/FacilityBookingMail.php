<?php

namespace App\Mail;

use App\Models\FacilityBooking\FacilityBookingRecurrence;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class FacilityBookingMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    /**
     * @var User $user
     */
    public $user;

    /**
     * @var FacilityBookingRecurrence $facilityBookingRecurrence
     */
    public $facilityBookingRecurrence;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $user, FacilityBookingRecurrence $facilityBookingRecurrence)
    {
        $this->user = $user;
        $this->facilityBookingRecurrence = $facilityBookingRecurrence;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('mails.FacilityBooking.new-facility-booking');
    }
}
