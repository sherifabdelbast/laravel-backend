<?php

namespace App\Repositories;

use App\Models\Issue;
use App\Models\Project;
use App\Models\ProjectHistory;
use App\Models\Team;
use App\Models\User;

class TeamRepository
{
    public function getTeamMemberByUserId($data)
    {
        return Team::query()
            ->where('project_id', '=', $data['project_id'])
            ->where('user_id', '=', $data['user_id'])
            ->first();
    }

    public function listOfTeamMember($data)
    {
        return Project::query()
            ->where('id', '=', $data['project_id'])
            ->with(['teamMembers.role', 'teamMembers.user'])
            ->first();
    }

    public function checkIfTeamMemberExists($data)
    {
        return Team::query()
            ->where('id', '=', $data['teamMember_id'])
            ->where('project_id', '=', $data['project_id'])
            ->withoutGlobalScope('access')
            ->first();
    }

    public function deleteTeamMember($data)
    {
        $teamMember = Team::query()
            ->find($data['teamMember_id']);
        $teamMember->delete();

        Issue::query()
            ->where('assign_to', '=', $data['teamMember_id'])
            ->update([
                'assign_to' => null
            ]);
        $this->storeDeleteTeamMemberInProjectHistory($data, $teamMember);
    }

    public function storeDeleteTeamMemberInProjectHistory($data, $teamMember)
    {
        ProjectHistory::query()
            ->create([
                'status' => 'Delete team member',
                'type' => 'Delete member',
                'action' => 'remove',
                'project_id' => $data['project_id'],
                'user_received_action' => $teamMember->user_id,
                'user_id' => $data['user_id']
            ]);
    }

    public function checkIfTeamMemberIsCreator($teamMember)
    {
        return Project::query()
            ->where('id', '=', $teamMember->project_id)
            ->where('user_id', '=', $teamMember->user_id)
            ->first();
    }

    public function listOfTeamMemberAccepted($data)
    {
        return Team::query()
            ->where('project_id', '=', $data['project_id'])
            ->where('access', '=', 1)
            ->with(['role', 'user'])
            ->get();
    }

    public function getAllTeamMemberOfAllprojects($projectsId)
    {
        $userIdToTeamMember = Team::query()
            ->whereIn('project_id', $projectsId)
            ->where('access', '=', 1)
            ->pluck('user_id')
            ->unique()
            ->toArray();

        return User::query()
            ->whereIn('id', $userIdToTeamMember)
            ->select([
                'id',
                'name',
                'email',
                'photo'
            ])
            ->get();
    }

    public function getAllTeamMemberSameRole($data)
    {
        return Team::query()
            ->where('project_id', '=', $data['project_id'])
            ->where('role_id', '=', $data['role_id'])
            ->get();
    }

    public function updateRoleToMember($data, $member, $role)
    {
        $member->update(['role_id' => $data['role_id']]);
        $member->assignRole($role);
    }

    public function updateRoleToMemberWhenDeleteRole($data, $member, $role)
    {
        $member->update(['role_id' => $data['new_role_id']]);
        $member->assignRole($role);
    }

    public function getAllTeamMemberByProjectId($projectId)
    {
        return Team::query()
            ->where('project_id','=',$projectId)
            ->where('access','=',1)
            ->get();
    }
}
