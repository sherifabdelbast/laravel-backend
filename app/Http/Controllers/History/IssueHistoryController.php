<?php

namespace App\Http\Controllers\History;

use App\Http\Controllers\Controller;
use App\Http\Requests\Issue\ShowIssueRequest;
use App\Models\IssueHistory;
use App\Repositories\IssueRepository;
use Illuminate\Http\Request;

class IssueHistoryController extends Controller
{
    public function index(ShowIssueRequest $request,
                          IssueRepository  $issueRepository,
                          IssueHistory     $issueHistory)
    {
        $data = $request->validated();
        $this->authorize('view', [$issueHistory, $data['project_id']]);
        $issues = $issueRepository->getIssueHistory($data);

        return response([
            'message' => 'list of Issue history',
            'code' => 200,
            'issue_history' => $issues['issue_history'],
            'total_pages' => $issues['total_pages'],
            'current_page' => $issues['current_page']
        ], 200);
    }
}
