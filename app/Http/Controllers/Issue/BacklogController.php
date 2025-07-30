<?php

namespace App\Http\Controllers\Issue;

use App\Http\Controllers\Controller;
use App\Http\Requests\Issue\BacklogRequest;
use App\Http\Requests\Issue\MoveIssueRequest;
use App\Models\Issue;
use App\Repositories\IssueRepository;

class BacklogController extends Controller
{
    public function index(BacklogRequest  $request,
                          IssueRepository $issueRepository)
    {
        $data = $request->validated();

        $sprintIssues = $issueRepository->getAllIssuesInThisProjectBelongToSprints($data);
        $backlogIssues = $issueRepository->getAllIssuesInThisProjectNotBelongToSprints($data);

        return response([
            'message' => 'This is a list of the issues in the backlog',
            'code' => 200,
            'sprints' => $sprintIssues,
            'backlog' => $backlogIssues
        ]);
    }

    public function update(MoveIssueRequest $request,
                           IssueRepository  $issueRepository,
                           Issue            $issue)
    {
        $data = $request->validated();
        $this->authorize('moveBacklog', [$issue, $data['project_id']]);
        $OrderMax = $data['order'];
        if ($data['order'] == 0) {
            if ($data['position'] == 0) {
                $OrderMax = $issueRepository->maxOrderBySprint($data['sprint_id']) + 1;
            } else {
                $OrderMax = 1;
            }
        }
        $issueRepository->moveIssue($data, $data['issue_id'], $OrderMax);
        return response([
            'message' => 'issue has been moved successfully',
            'code' => 200
        ], 200);
    }
}
