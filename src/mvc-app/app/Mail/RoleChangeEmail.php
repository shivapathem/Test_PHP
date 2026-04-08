<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RoleChangeEmail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public $userFullname;
    public $oldRoleName;
    public $newRoleName;
    public $teamsname;
    public $requesterName;

    public function __construct(string $userFullname, string $oldRoleName, string $newRoleName, string $teamsname, string $requesterName)
    {
        $this->userFullname = $userFullname;
        $this->oldRoleName = $oldRoleName;
        $this->newRoleName = $newRoleName;
        $this->teamsname = $teamsname;
        $this->requesterName = $requesterName;
    }

    public function build()
    {
        return $this->subject($this->getSubject())
            ->view('mails.role-change-email');
    }

    /**
     * Generate email subject for role change notification
     *
     * @return string
     */
    private function getSubject(): string
    {
        $emailSuffix = env('EMAIL_SUFFIX_N', '');
        $prefix = $emailSuffix ? $emailSuffix . ' ' : '';
        return $prefix . 'Allocate 7: ' . $this->userFullname . ' - Role Updated by ' . $this->requesterName . ' from ' . $this->oldRoleName . ' to ' . $this->newRoleName;
    }
}
