<?php

namespace App\Http\Controllers\Team;

use App\Http\Controllers\Controller;
use App\Http\Requests\Team\ListTeamRequest;
use App\Repositories\TeamRepository;

class TeamMembersAcceptedController extends Controller
{
    public function index(ListTeamRequest $request,
                          TeamRepository  $teamRepository)
    {
        $data = $request->validated();
        $acceptedTeamMember = $teamRepository->listOfTeamMemberAccepted($data);
        return response(
            [
                'message' => 'list of team member accepted the invitation',
                'code' => 200,
                'teamMember' => $acceptedTeamMember
            ], 200);
    }
}
