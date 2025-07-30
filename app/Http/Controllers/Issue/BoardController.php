<?php

namespace App\Http\Controllers\Issue;

use App\Http\Controllers\Controller;
use App\Http\Requests\Issue\BoardListIssueRequest;
use App\Http\Requests\Issue\MoveIssueByStatusRequest;
use App\Models\Issue;
use App\Repositories\IssueRepository;
use App\Repositories\ProjectRepository;
use App\Repositories\StatusRepository;

class BoardController extends Controller
{
    public function index(BoardListIssueRequest $request,
                          StatusRepository      $statusRepository,
                          ProjectRepository     $projectRepository)
    {
        $data = $request->validated();
        $projectId = $data['project_id'];
        $boardList = $statusRepository->listOfStatusesWithIssue($projectId, $data);
        $sprint = $statusRepository->getSprintInTheProject($projectId);
        $projectRepository->lastProjectOpenDefault($projectId, $data['user_id']);

        return response([
            'message' => 'Here is your board.',
            'code' => 200,
            'sprint' => $sprint,
            'board' => $boardList
        ]);
    }

    public function update(MoveIssueByStatusRequest $request,
                           IssueRepository          $issueRepository,
                           Issue                    $issue)
    {
        $data = $request->validated();
        $this->authorize('moveBoard', [$issue, $data['project_id']]);
        $issueRepository->moveIssueByStatus($data);

        return response([
            'message' => 'issue has been moved successfully',
            'code' => 200
        ], 200);
    }
}
