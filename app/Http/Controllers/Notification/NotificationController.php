<?php

namespace App\Http\Controllers\Notification;

use App\Http\Controllers\Controller;
use App\Http\Requests\Notification\AllNotificationRequest;
use App\Http\Requests\Notification\DeleteNotificationRequest;
use App\Http\Resources\RecipientResource;
use App\Repositories\NotificationRepository;

class NotificationController extends Controller
{
    public function index(AllNotificationRequest $request,
                          NotificationRepository $notificationRepository)
    {
        $data = $request->validated();
        $notifications = $notificationRepository->getAllNotificationToThisUser($data['user_id']);
//        $notificationsResource = RecipientResource::collection($notifications);
        if (isset($data['read_all'])) {
            foreach ($notifications as $notification) {
                $notification->update(['read_at' => now()]);
            }
        }
        return response([
            'message' => 'All notification',
            'code' => 200,
            'notifications' => $notifications
        ], 200);
    }

    public function update(NotificationRepository $notificationRepository)
    {
        $recipientId = request()->segment(3);
        $notificationRepository->getRecipientById($recipientId)
            ->update(['read_at' => now()]);

        return response([
            'message' => 'Notification read successfully',
            'code' => 200
        ], 200);
    }

    public function destroy(DeleteNotificationRequest $request,
                            NotificationRepository    $notificationRepository)
    {
        $data = $request->validated();

        $notificationReceived = $notificationRepository->getRecipient($data);
        $notificationRepository->deleteNotification($notificationReceived);
        return response([
            'message' => 'Notification deleted successfully',
            'code' => 200,
        ], 200);
    }
}
