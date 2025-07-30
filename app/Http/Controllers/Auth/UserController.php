<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Repositories\NotificationRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function show(UserRepository         $userRepository,
                         NotificationRepository $notificationRepository)
    {
        $user = Auth::user();
        $clipboard = $userRepository->defaultProject($user->id);
        $countOfNotifications = $notificationRepository->getAllNotificationToThisUser($user->id)
            ->whereNull('read_at')->count();
        return [
            'id' => $user->id,
            'identify_number' => $user->identify_number,
            'name' => $user->name,
            'email' => $user->email,
            'url_photo' => $user->url_photo,
            'count_of_notifications' => $countOfNotifications,
            'project_id' => $clipboard?->project_id ?? null,
            'project_identify' => $clipboard->project->project_identify ?? null,
        ];
    }
}
