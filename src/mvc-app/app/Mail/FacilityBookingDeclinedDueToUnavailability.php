<?php

namespace App\Mail;

use App\Models\FacilityBooking\FacilityBooking;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class FacilityBookingDeclinedDueToUnavailability extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    /** @var \App\Models\User */
    public $user;

    /** @var \App\Models\FacilityBooking\FacilityBooking */
    public $facilityBooking;

    /** @var \Illuminate\Support\Collection<string> Comma-joinable dd/mm/YYYY dates */
    public $declinedDates;

    /** @var string|null */
    public $reason;

    public $bookings;

    /**
     * Mirror the cancellation mailer's constructor style.
     *
     * @param \App\Models\User                       $user             Recipient user (creator)
     * @param \App\Models\FacilityBooking\FacilityBooking $facilityBooking  A representative/primary booking from the recurrence
     * @param \Illuminate\Support\Collection<string> $declinedDates    Collection of 'd/m/Y' strings of declined instance dates
     * @param string|null                            $reason           Optional group-level decline reason
     */
    public function __construct(User $user, FacilityBooking $facilityBooking, Collection $declinedDates, ?string $reason = null, ?Collection $bookings = null)
    {
        $this->user            = $user;
        $this->facilityBooking = $facilityBooking;
        $this->declinedDates   = $declinedDates->values(); // keep clean, unique in blade
        $this->reason          = $reason;
        $this->bookings = $bookings
            ? $bookings->filter()->unique('FB_FacilityBookingID')->sortBy(fn($b) => $b->FB_BookingStartDateTime)->values()
            : null;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        // Subject line kept simple—set at callsite if you want recurrence title included.
        return $this->subject('Facility booking(s) declined due to unavailability')
            ->view('mails.FacilityBooking.declined-due-to-unavailability');
    }
}
