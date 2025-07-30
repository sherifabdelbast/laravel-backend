<?php

namespace App\Http\Controllers\Sprint;

use App\Http\Controllers\Controller;
use App\Http\Requests\Sprint\CompleteSprintRequest;
use App\Http\Requests\Sprint\EditSprintRequest;
use App\Jobs\PushNotificationJob;
use App\Models\Sprint;
use App\Repositories\NotificationRepository;
use App\Repositories\SprintRepository;
use App\Repositories\TeamRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\DB;

class StartSprintController extends Controller
{
    public function update(EditSprintRequest      $request,
                           SprintRepository       $sprintRepository,
                           TeamRepository         $teamRepository,
                           UserRepository         $userRepository,
                           NotificationRepository $notificationRepository,
                           Sprint                 $sprint)
    {
        $data = $request->validated();
        $this->authorize('start', [$sprint, $data['project_id']]);
        DB::beginTransaction();
        try {

            $teams = $teamRepository->listOfTeamMemberAccepted($data)
                ->pluck('user_id')
                ->toArray();
            $type = 'sprint';
            $title = 'Start Sprint';
            $userWhoDid = $userRepository->getUserById($data['user_id']);
            $projectIdentify = $data['project_identify'];
            $url = "https://www.taskat.approved.tech/projects/$projectIdentify/backlog";
            if (!empty($teams)) {
                $content = $userWhoDid->name . ' Started Sprint';
                $notification = $notificationRepository
                    ->storeSprintNotification($data, $type, $title, 'Start', $content);
                foreach ($teams as $team) {
                    $user = $userRepository->getUserById($team);
                    if ($user->id != auth()->id() && $user->player_ids != null) {
                        $notificationRepository->storeRecipient($notification->id, $user->id);
                        PushNotificationJob::dispatch($user, $title, $content, $url);
                    }
                }
            }
            $sprintRepository->startSprint($data);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
        return response([
            'message' => 'Sprint has been started successfully.',
            'code' => 200
        ], 200);
    }

    public function destroy(CompleteSprintRequest  $request,
                            SprintRepository       $sprintRepository,
                            TeamRepository         $teamRepository,
                            UserRepository         $userRepository,
                            NotificationRepository $notificationRepository,
                            Sprint                 $sprint)
    {
        $data = $request->validated();

        $this->authorize('complete', [$sprint, $data['project_id']]);
        DB::beginTransaction();
        try {
            $teams = $teamRepository->listOfTeamMemberAccepted($data)
                ->pluck('user_id')
                ->toArray();
            $type = 'sprint';
            $title = 'Completed Sprint';
            $userWhoDid = $userRepository->getUserById($data['user_id']);
            $projectIdentify = $data['project_identify'];
            $url = "https://www.taskat.approved.tech/projects/$projectIdentify/backlog";
            if (!empty($teams)) {
                $content = $userWhoDid->name . ' Completed Sprint';
                $notification = $notificationRepository
                    ->storeSprintNotification($data, $type, $title, 'Completed', $content);
                foreach ($teams as $team) {
                    $user = $userRepository->getUserById($team);
                    if ($user->id != auth()->id() && $user->player_ids != null) {
                        $notificationRepository->storeRecipient($notification->id, $user->id);
                        PushNotificationJob::dispatch($user, $title, $content, $url);
                    }
                }
            }
            $sprintRepository->completeSprint($data);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
        return response([
            'message' => 'Sprint has been completed successfully.',
            'code' => 200
        ], 200);
    }
}
