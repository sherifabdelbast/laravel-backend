<?php

namespace App\Repositories;

use App\Models\Invitation;
use App\Models\Project;
use App\Models\ProjectHistory;
use App\Models\RequestHistory;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Str;

class InvitationRepository
{
    public function checkUserExistsByEmail($email)
    {
        return User::query()
            ->where('email', '=', $email)
            ->first();
    }

    public function checkUserTeamMember($projectId, $userId)
    {
        return Team::query()
            ->where('project_id', '=', $projectId)
            ->where('user_id', '=', $userId)
            ->withoutGlobalScope('access')
            ->first();
    }

    public function checkAcceptInvitation($memberId)
    {
        return Team::query()
            ->where('id', '=', $memberId)
            ->where('access', '=', 1)
            ->first();
    }

    public function checkLastInvitation($projectId, $teamMemberId)
    {
        return Invitation::query()
            ->where('project_id', '=', $projectId)
            ->where('member_id', '=', $teamMemberId)
            ->latest('id')
            ->first();
    }

    public function checkIfTheLoggedInUserIsTheInvitee($userAuth, $teamMember)
    {
        $team = $this->checkIfTeamMemberExists($teamMember);
        if ($team->user_id === $userAuth->id) {
            return true;
        }
        return false;
    }

    public function countOfRequestInOneDay($projectId, $teamMemberId)
    {
        return Invitation::query()
            ->where('project_id', '=', $projectId)
            ->where('member_id', '=', $teamMemberId)
            ->whereDate('created_at', '=', now()->format('y-m-d'))
            ->count();
    }

    public function storeInvitation($data, $message, $teamMemberId)
    {
        $inviteIdentify = Str::uuid();
        while (Invitation::query()
            ->where('invite_identify', '=', $inviteIdentify)
            ->exists()
        ) {
            $inviteIdentify = Str::uuid();
        }
        $this->storeTeamMemberInProjectHistory($data, $teamMemberId);

        return Invitation::query()
            ->create([
                'invite_identify' => $inviteIdentify,
                'message' => $message,
                'member_id' => $teamMemberId,
                'project_id' => $data['project_id'],
                'role_id' => $data['role_id'],
                'user_id' => $data['user_id'],
            ]);
    }

    public function storeTeamMemberInProjectHistory($data, $teamMemberId)
    {
        $team = Team::query()
            ->where('id', '=', $teamMemberId)
            ->withoutGlobalScope('access')
            ->first();

        return ProjectHistory::query()
            ->create([
                'status' => 'Invite team member',
                'type' => 'invite member',
                'action'=>'invite',
                'project_id' => $data['project_id'],
                'user_received_action' => $team->user_id,
                'user_id' => $data['user_id']
            ]);
    }

    public function storeTeamMemberAndInvitation($data, $message, $userId)
    {
        $teamMember = Team::query()
            ->create([
                'project_id' => $data['project_id'],
                'role_id' => $data['role_id'],
                'user_id' => $userId
            ]);
        return $this->storeInvitation($data, $message, $teamMember->id);
    }

    public function createNewUserAndSendInvitation($email)
    {
        $identifyNumber = Str::uuid();
        while (User::query()
            ->where('identify_number', '=', $identifyNumber)
            ->exists()) {
            $identifyNumber = Str::uuid();
        }
        return User::query()
            ->create([
                'email' => $email,
                'identify_number' => $identifyNumber
            ]);
    }

    public function checkInvitationByinviteIdentify($inviteIdentify)
    {
        return Invitation::query()
            ->where('invite_identify', '=', $inviteIdentify)
            ->first();
    }

    public function invitationIsAlreadyUsed($projectId)
    {
        return Invitation::query()
            ->where('invite_identify', '=', $projectId)
            ->where('previously_used', '=', 1)
            ->first();
    }

    public function checkIfTeamMemberExists($teamMember)
    {
        return Team::query()
            ->where('id', '=', $teamMember)
            ->withoutGlobalScope('access')
            ->first();
    }

    public function acceptInvitation($data, $checkIfTeamMemberExists, $inviteIdentify)
    {
        Invitation::query()
            ->where('invite_identify', '=', $inviteIdentify)
            ->update([
                'previously_used' => 1
            ]);
        return $checkIfTeamMemberExists->update(
            [
                'access' => $data['accept'],
                'invite_status' => 'accept'
            ]);
    }

    public function rejectInvitation($data, $checkIfTeamMemberExists, $inviteIdentify)
    {
        Invitation::query()
            ->where('invite_identify', '=', $inviteIdentify)
            ->update([
                'previously_used' => 1
            ]);

        return $checkIfTeamMemberExists->update([
            'access' => $data['accept'],
            'invite_status' => 'reject'
        ]);
    }

    public function lastInvitationRequest($projectId, $teamMember)
    {
        return Invitation::query()
            ->where('project_id', '=', $projectId)
            ->where('member_id', '=', $teamMember)
            ->latest('id')
            ->first();
    }

    public function projectInvitation($inviteIdentify)
    {
        return Project::query()
            ->where('id', '=', $inviteIdentify)
            ->first();
    }

    public function getUser($teamMember)
    {
        $team = Team::query()
            ->where('id', '=', $teamMember)
            ->withoutGlobalScope('access')
            ->first();

        return User::query()
            ->where('id', '=', $team->user_id)
            ->first();
    }

    public function sendRequestToCompleteRegister($identifyNumber)
    {
        $lastRequest = RequestHistory::query()
            ->where('identify_number', '=', $identifyNumber)
            ->latest('id')
            ->first();
        $lastRequest?->delete();

        return RequestHistory::query()
            ->create([
                'identify_number' => $identifyNumber,
                'token' => bcrypt(Str::random(10)),
                'expired_at' => now()->addMinutes(15)
            ]);
    }
}
