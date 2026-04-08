<?php

namespace App\Mail\FacilityBooking;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use App\Models\FacilityBooking\FacilityBooking;
use App\Models\User;

class FacilityBookingUpdateMail extends Mailable implements ShouldQueue
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
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $user, Collection $updatedBookings)
    {
        $this->updatedBookings = $updatedBookings;
        $this->user = $user;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Facility Booking Update',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $this->bookingCreatedByuser = $this->updatedBookings->first()->createdBy;
        $this->firstBooking = $this->updatedBookings->first();
        return new Content(
            view: 'mails.FacilityBooking.update-facility-booking',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
