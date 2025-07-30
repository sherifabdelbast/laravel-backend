<?php

namespace App\Http\Controllers\Team;

use App\Http\Controllers\Controller;
use App\Http\Requests\Team\DeleteTeamRequest;
use App\Http\Requests\Team\ListTeamRequest;
use App\Http\Requests\Team\UpdateRoleTeamMember;
use App\Jobs\PushNotificationJob;
use App\Models\Team;
use App\Repositories\NotificationRepository;
use App\Repositories\ProjectRepository;
use App\Repositories\RoleRepository;
use App\Repositories\TeamRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\DB;
use function PHPUnit\Framework\isEmpty;

class TeamController extends Controller
{
    public function index(ListTeamRequest $request,
                          TeamRepository  $teamRepository)
    {
        $data = $request->validated();
        $listOfTeamMember = $teamRepository->listOfTeamMember($data);
        return response([
            'message' => 'List of team members retrieved successfully.',
            'code' => 200,
            'data' => $listOfTeamMember
        ], 200);
    }

    public function update(UpdateRoleTeamMember   $request,
                           TeamRepository         $teamRepository,
                           RoleRepository         $roleRepository,
                           UserRepository         $userRepository,
                           NotificationRepository $notificationRepository,
                           Team                   $team)
    {
        $data = $request->validated();

        $this->authorize('update', [$team, $data['project_id']]);
        DB::beginTransaction();
        try {
            $role = $roleRepository->getRoleById($data['role_id']);
            $member = $teamRepository->checkIfTeamMemberExists($data);
            $user = $userRepository->getUserById($member->user_id);
            $teamRepository->updateRoleToMember($data, $member, $role);
            if ($user->plyer_ids != null) {
                $title = 'Change Role';
                $content = 'you role change to' . $role->key;
                $projectIdentify = $data['project_identify'];
                $url = "https://www.taskat.approved.tech/projects/$projectIdentify/backlog";
                $notification = $notificationRepository
                    ->storeInvitationNotification($data, 'Change Role',
                        $title, 'Change Role', $content, null);
                $this->storeRecipient($notification->id, $user->id);
                PushNotificationJob::dispatch($user, $title, $content, $url);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
        return response([
            'message' => 'Update role to teamMembers successfully.',
            'code' => 200,
        ], 200);
    }

    public function destroy(DeleteTeamRequest      $request,
                            TeamRepository         $teamRepository,
                            ProjectRepository      $projectRepository,
                            UserRepository         $userRepository,
                            NotificationRepository $notificationRepository,
                            Team                   $team)
    {
        $data = $request->validated();
        $userWhoDid = $userRepository->getUserById($data['user_id']);
        $this->authorize('delete', [$team, $data['project_id']]);
        DB::beginTransaction();
        try {
            $teamMember = $teamRepository->checkIfTeamMemberExists($data);
            $project = $projectRepository->getProjectById($data['project_id']);
            $users = [$project->user_id, $teamMember->user_id];

            $title = 'removed form project';
            $content = $userWhoDid->name . ' removed team member';
            $url = "https://www.taskat.approved.tech/projects";
            if (!empty($users)) {
                $notification = $notificationRepository
                    ->storeInvitationNotification($data, 'Remove member',
                        $title, 'Remove member', $content, null);
                foreach ($users as $userId) {
                    $user = $userRepository->getUserById($userId);
                    if ($user->plyer_ids != null) {
                        $notification->storeRecipient($notification->id, $userId);
                        PushNotificationJob::dispatch($user, $title, $content, $url);
                    }
                    $content = $userWhoDid->name . ' remove you form project ' . $project->name;
                }
            }
            $teamRepository->deleteTeamMember($data);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
        return response([
            'message' => 'The team member has been deleted successfully',
            'code' => 200
        ], 200);
    }
}
