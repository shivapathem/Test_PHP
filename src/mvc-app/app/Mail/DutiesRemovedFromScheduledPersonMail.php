<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DutiesRemovedFromScheduledPersonMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    /**
     * @var string $scheduledPersonName - Full name of the scheduled person being removed
     */
    public $scheduledPersonName;

    /**
     * @var string $schedulingTeamName - Name of the scheduling team
     */
    public $schedulingTeamName;

    /**
     * @var string $adminPreferredForename - Preferred forename of the team admin (for greeting)
     */
    public $adminPreferredForename;

    /**
     * @var array $deletedDuties - Array of duties marked as "doesn't need covering"
     */
    public $deletedDuties;

    /**
     * @var array $unallocatedDuties - Array of duties moved to unallocated status
     */
    public $unallocatedDuties;

    /**
     * @var string|null $ccEmail - Email address to CC (user who pressed Delete)
     */
    public $ccEmail;

    /**
     * Create a new message instance.
     *
     * @param string $scheduledPersonName - Full name of scheduled person
     * @param string $schedulingTeamName - Name of scheduling team
     * @param string $adminPreferredForename - Team admin's preferred forename for greeting
     * @param array $deletedDuties - Duties marked as "doesn't need covering"
     * @param array $unallocatedDuties - Duties moved to unallocated status
     * @param string|null $ccEmail - Email address of user who pressed Delete (for CC)
     */
    public function __construct(
        string $scheduledPersonName,
        string $schedulingTeamName,
        string $adminPreferredForename,
        array $deletedDuties,
        array $unallocatedDuties,
        ?string $ccEmail = null
    ) {
        $this->scheduledPersonName = $scheduledPersonName;
        $this->schedulingTeamName = $schedulingTeamName;
        $this->adminPreferredForename = $adminPreferredForename;
        $this->deletedDuties = $deletedDuties;
        $this->unallocatedDuties = $unallocatedDuties;
        $this->ccEmail = $ccEmail;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subject = "Unallocated duties in {$this->schedulingTeamName} because {$this->scheduledPersonName} has been removed";

        $mailable = $this->subject($subject)
            ->markdown('emails.scheduling.duties-removed');

        // Add CC if email address provided
        if (!empty($this->ccEmail)) {
            $mailable->cc($this->ccEmail);
        }

        return $mailable;
    }
}
