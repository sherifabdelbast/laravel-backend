<?php

namespace App\Http\Controllers\Issue;

use App\Http\Controllers\Controller;
use App\Models\Issue;
use App\Repositories\IssueRepository;

class TypeIssueController extends Controller
{
    public function index(IssueRepository $issueRepository)
    {
        $projectId = request()->segment(3);
        $issueId = request()->segment(5);

        $issueRepository->getTypeIssue($projectId, $issueId);

        return response([
            'message' => 'get all type issue',
            'code' => 200,
            'type' => ['task', 'bug', 'story']
        ], 200);
    }
}
