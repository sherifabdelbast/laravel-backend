<?php

namespace App\Jobs;

use App\Notifications\InvitationNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendInvitationEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    protected $user;
    protected $inviteIdentify;

    public function __construct($user, $inviteIdentify)
    {
        $this->user = $user;
        $this->inviteIdentify = $inviteIdentify;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $user = $this->user;
        $inviteIdentify = $this->inviteIdentify;
        $user->notify(new InvitationNotification($inviteIdentify));
    }
}
