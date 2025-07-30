<?php

namespace App\Http\Controllers\Team;

use App\Http\Controllers\Controller;
use App\Http\Requests\Team\AcceptInvitationRequest;
use App\Http\Requests\Team\InviteRequest;
use App\Jobs\PushNotificationJob;
use App\Jobs\SendInvitationEmail;
use App\Models\Team;
use App\Repositories\InvitationRepository;
use App\Repositories\NotificationRepository;
use App\Repositories\ProjectRepository;
use App\Repositories\UserRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

## TODO:: This code needs review and improvement ^_^
class InvitationController extends Controller
{
    public function store(InviteRequest          $request,
                          InvitationRepository   $invitationRepository,
                          UserRepository         $userRepository,
                          NotificationRepository $notificationRepository,
                          Team                   $team)
    {
        $data = $request->validated();
        $url = "https://www.taskat.approved.tech/invitation";

        $this->authorize('invite', [$team, $data['project_id']]);
        $userWhoDid = $userRepository->getUserById($data['user_id']);
        $email = $data['email'];
        $projectId = $data['project_id'];
        $message = null;
        if (isset($data['message'])) {
            $message = $data['message'];
        }
        $title = 'invite to you';
        $content = $userWhoDid->name . ' invited you to the project';
        $type = 'invite member';
        $action = 'invite';

        $checkUserExists = $invitationRepository->checkUserExistsByEmail($email);
        if ($checkUserExists) {
            $checkTeamMember = $invitationRepository->checkUserTeamMember($projectId, $checkUserExists->id);
            if ($checkTeamMember) {
                $checkAcceptInvitation = $invitationRepository->checkAcceptInvitation($checkTeamMember->id);
                if ($checkAcceptInvitation) {
                    return response([
                        'message' => 'This member is already part of the team.',
                        'code' => 400
                    ], 400);
                }
                $lastInvitation = $invitationRepository->checkLastInvitation($projectId, $checkTeamMember->id);
                $checkResendInvitation = Carbon::parse($lastInvitation->created_at)->addMinutes(15)->isPast();
                if (!$checkResendInvitation) {
                    return response([
                        'message' => 'Please wait for 15 minutes before sending another invitation.',
                        'code' => 400
                    ], 400);
                }

                $countOfRequestInOneDay = $invitationRepository
                    ->countOfRequestInOneDay($projectId, $checkTeamMember->id);
                if ($countOfRequestInOneDay === 3) {
                    return response([
                        'message' => 'You have reached the maximum number of invitation attempts. Try again later.',
                        'code' => 429
                    ], 429);
                }
                $invitation = $invitationRepository->storeInvitation($data, $message, $checkTeamMember->id);
                $inviteIdentify = $invitation->invite_identify;
                SendInvitationEmail::dispatch($checkUserExists, $inviteIdentify);

                if ($checkUserExists->player_ids != null) {
                    $notification = $notificationRepository
                        ->storeInvitationNotification($data, $type,
                            $title, $action, $content, $invitation->id);
                    $notificationRepository->storeRecipient($notification->id, $checkUserExists->id);
                    PushNotificationJob::dispatch($checkUserExists, $title, $content, $url);
                }
                return response([
                    'message' => 'Invitation sent successfully.',
                    'code' => 200
                ], 200);
            }
            $storeTeamMemberAndInvitation = $invitationRepository
                ->storeTeamMemberAndInvitation($data, $message, $checkUserExists->id);
            $inviteIdentify = $storeTeamMemberAndInvitation->invite_identify;
            SendInvitationEmail::dispatch($checkUserExists, $inviteIdentify);
            if ($checkUserExists->player_ids != null) {
                $notification = $notificationRepository
                    ->storeInvitationNotification($data, $type,
                        $title, $action, $content, $storeTeamMemberAndInvitation->id);
                $notificationRepository->storeRecipient($notification->id, $checkUserExists->id);
                PushNotificationJob::dispatch($checkUserExists, $title, $content, $url);
            }
            return response([
                'message' => 'Invitation sent successfully.',
                'code' => 200
            ], 200);
        }
        $user = $invitationRepository->createNewUserAndSendInvitation($email);
        $storeTeamMemberAndInvitation = $invitationRepository
            ->storeTeamMemberAndInvitation($data, $message, $user->id);
        $inviteIdentify = $storeTeamMemberAndInvitation->invite_identify;
        SendInvitationEmail::dispatch($user, $inviteIdentify);
        if ($user->player_ids != null) {
            $notification = $notificationRepository
                ->storeInvitationNotification($data, $type,
                    $title, $action, $content, $storeTeamMemberAndInvitation->id);
            $notificationRepository->storeRecipient($notification->id, $user->id);
            PushNotificationJob::dispatch($user, $title, $content, $url);
        }
        return response([
            'message' => 'Invitation sent successfully.',
            'code' => 200
        ], 200);
    }

    public function update(AcceptInvitationRequest $request,
                           InvitationRepository    $invitationRepository,
                           NotificationRepository  $notificationRepository,
                           UserRepository          $userRepository,
                           ProjectRepository       $projectRepository)
    {
        $data = $request->validated();
        $userId = auth()->id();
        $inviteIdentify = $data['invite_identify'];
        $checkInvitation = $invitationRepository->checkInvitationByinviteIdentify($inviteIdentify);
        $type = 'invite member';
        $action = 'invite';
        $project = $invitationRepository->projectInvitation($inviteIdentify);
        $projectIdentify = $project->project_identify;
        $url = "https://www.taskat.approved.tech/projects/$projectIdentify/backlog";
        $userWhoDid = $userRepository->getUserById($data['user_id']);
        $inviter = $userRepository->getUserById($checkInvitation->user_id);
        DB::beginTransaction();
        try {
            if ($checkInvitation) {
                $invitationIsAlreadyUsed = $invitationRepository->invitationIsAlreadyUsed($inviteIdentify);

                if ($invitationIsAlreadyUsed) {
                    return response([
                        "message" => "You have already accepted or declined this invitation.",
                        "code" => 400
                    ], 400);
                }
                $checkIfTeamMemberExists = $invitationRepository->checkIfTeamMemberExists($checkInvitation->member_id);
                if ($data['accept'] == 1) {
                    if ($inviter->player_ids != null) {
                        $title = 'Invitation Notification';
                        $content = $userWhoDid->name . ' accepted the invitation';
                        $notification = $notificationRepository
                            ->storeInvitationNotification($data, $type,
                                $title, $action, 'Accepted the invitation', $checkInvitation->id);
                        $notificationRepository->storeRecipient($notification->id, $inviter->id);
                        PushNotificationJob::dispatch($inviter, $title, $content, $url);
                    }
                    $projectRepository->lastProjectOpenDefault($checkIfTeamMemberExists->project_id, $userId);
                    $invitationRepository->acceptInvitation($data, $checkIfTeamMemberExists, $inviteIdentify);
                    $project = $invitationRepository->projectInvitation($checkIfTeamMemberExists->project_id);
                    return response([
                        "message" => "You have successfully accepted this invitation.",
                        "code" => 200,
                        "project" => $project
                    ]);
                }

                $invitationRepository->rejectInvitation($data, $checkIfTeamMemberExists, $inviteIdentify);
                if ($inviter->player_ids != null) {
                    $title = 'Invitation Notification';
                    $content = $userWhoDid->name . ' rejected the invitation';
                    $notification = $notificationRepository
                        ->storeInvitationNotification($data, $type,
                            $title, $action, 'rejected the invitation', $checkInvitation->id);
                    $notificationRepository->storeRecipient($notification->id, $inviter->id);
                    PushNotificationJob::dispatch($inviter, $title, $content, $url);
                }
                return response([
                    "message" => "You have successfully declined this invitation.",
                    "code" => 200
                ]);
            }
            DB::commit();
        } catch (\Exception $e) {

            DB::rollBack();
            throw $e;
        }

        return response([
            "message" => "This invitation does not exist.",
            "code" => 400
        ], 400);
    }
}
