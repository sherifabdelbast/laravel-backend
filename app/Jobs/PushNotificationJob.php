<?php

namespace App\Jobs;

use App\Repositories\NotificationRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PushNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    protected $user;
    protected $title;
    protected $content;
    protected $url;

    public function __construct($user, $title, $content, $url)
    {
        $this->user = $user;
        $this->title = $title;
        $this->content = $content;
        $this->url = $url;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $user = $this->user;
        $title = $this->title;
        $content = $this->content;
        $url = $this->url;

        $notificationRepository = new NotificationRepository();
        $countOfNotifications = $notificationRepository->getAllNotificationToThisUser($user->id)
            ->whereNull('read_at')->count();

        pushNotifications($user->player_ids, $title, $content, $url,$countOfNotifications);
    }
}
