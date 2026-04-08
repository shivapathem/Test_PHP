<?php

namespace App\Mail\Facility;

use App\Models\Facility\Facility;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class FacilityAdministratorUpdatedMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    /** @var \App\Models\User */
    public $user;

    /** @var Facility */
    public $facility;

    /** @var Collection */
    public $existingFacilityAdmins;

    /** @var Collection */
    public $newFacilityAdmins;

    /**
     * Create a new message instance.
     */
    public function __construct(Facility $facility, User $user, Collection $existingFacilityAdmins, Collection $newFacilityAdmins)
    {
        $this->facility = $facility;
        $this->user = $user;
        $this->existingFacilityAdmins = $existingFacilityAdmins;
        $this->newFacilityAdmins = $newFacilityAdmins;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Facility Administrator Updated - ' . $this->facility->FC_FacilityName,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'mails.facility.facility-administrator-update-mail',
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
