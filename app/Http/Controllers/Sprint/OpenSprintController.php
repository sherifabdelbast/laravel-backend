<?php

namespace App\Http\Controllers\Sprint;

use App\Http\Controllers\Controller;
use App\Http\Requests\Issue\BoardListIssueRequest;
use App\Repositories\SprintRepository;

class OpenSprintController extends Controller
{
    public function index(BoardListIssueRequest $request,
                          SprintRepository      $sprintRepository)
    {
        $data = $request->validated();
        $sprint = $sprintRepository->getAllSprintInThisProject($data['project_id'])
            ->where('is_open', '=', 1)
            ->select(['id', 'name'])
            ->get();
        return response([
            'message' => 'all open sprints',
            'code' => 200,
            'sprints' => $sprint
        ], 200);
    }
}
