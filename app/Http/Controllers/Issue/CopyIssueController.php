<?php

namespace App\Http\Controllers\Issue;

use App\Http\Controllers\Controller;
use App\Http\Requests\Issue\CopyIssueRequest;
use App\Models\Issue;
use App\Repositories\IssueRepository;

class CopyIssueController extends Controller
{
    public function store(CopyIssueRequest $request,
                          IssueRepository  $issueRepository,
                          Issue            $issue)
    {
        $data = $request->validated();
        $this->authorize('create', [$issue, $data['project_id']]);
        $issue = $issueRepository->checkIssueExists($data['project_id'], $data['issue_id']);
        $issueRepository->copyIssue($data, $issue);
        return response([
            'message' => 'Issues Copy successfully',
            'code' => 200
        ], 200);
    }
}
