<?php

namespace App\Mail;

use App\Models\Scheduling\SchedulingGroup;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SchedulingGroupDeletedMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    /**
     * @var SchedulingGroup $schedulingGroup
     */
    public $schedulingGroup;

    /**
     * @var User $deletedBy
     */
    public $deletedBy;

    /**
     * @var User $recipient
     */
    public $recipient;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(SchedulingGroup $schedulingGroup, User $deletedBy, User $recipient)
    {
        $this->schedulingGroup = $schedulingGroup;
        $this->deletedBy = $deletedBy;
        $this->recipient = $recipient;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Scheduling Group ' . $this->schedulingGroup->SchedulingGroupsName . ' has been deleted from Allocate')
                    ->view('mails.SchedulingGroup.deleted');
    }
}
