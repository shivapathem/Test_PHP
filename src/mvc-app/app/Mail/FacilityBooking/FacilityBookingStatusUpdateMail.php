<?php

namespace App\Mail\FacilityBooking;

use App\Models\FacilityBooking\FacilityBooking;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class FacilityBookingStatusUpdateMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    /**
     * @var User $user booking created user
     */
    public $bookingCreatedByuser;

    /**
     * @var FacilityBooking $firstBooking
     */
    public $firstBooking;

    /**
     * @var User $user user performing the action
     */
    public $user;

    /**
     * @var Collection $updatedBookings
     */
    public $updatedBookings;

    /**
     * @var string $statusChanged
     */
    public $statusChanged;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $user, Collection $updatedBookings, string $statusChanged)
    {
        $this->updatedBookings = $updatedBookings;
        $this->user = $user;
        $this->statusChanged = $statusChanged;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $this->bookingCreatedByuser = $this->updatedBookings->first()->createdBy;
        $this->firstBooking = $this->updatedBookings->first();

        if ($this->statusChanged == FacilityBooking::BOOKING_STATUS_DECLINED) {
            // Status declined
            return $this->view('mails.FacilityBooking.update-facility-booking-declined');
        }
        // For Confirmed status
        return $this->view('mails.FacilityBooking.update-facility-booking-confirmed');
    }
}
