<?php

namespace App\Http\Controllers\Team;

use App\Http\Controllers\Controller;
use App\Repositories\IssueRepository;
use App\Repositories\TeamRepository;

class AllTeamMembersOfAllProjects extends Controller
{
    public function index(TeamRepository  $teamRepository,
                          IssueRepository $issueRepository)
    {
        $userId = auth()->id();
        $projectsId = $issueRepository->getProject($userId);
        $teamMembers = $teamRepository->getAllTeamMemberOfAllprojects($projectsId);

        return response([
            'message' => 'list of all teamMembers of all projects',
            'code' => 200,
            'teamMembers' => $teamMembers
        ],200);
    }
}
