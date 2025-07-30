<?php

namespace App\Http\Controllers\Issue;

use App\Http\Controllers\Controller;
use App\Http\Requests\Issue\MoveBulkOfIssuesRequest;
use App\Models\Issue;
use App\Repositories\IssueRepository;
use Illuminate\Http\Request;

class MoveBulkOfIssuesController extends Controller
{
    public function update(MoveBulkOfIssuesRequest $request,
                           IssueRepository         $issueRepository,
                           Issue                     $issue)
    {
        $data = $request->validated();
        $this->authorize('moveBacklog', [$issue, $data['project_id']]);

        $issuesId = $data['issues_id'];
        $i = 0;
        foreach ($issuesId as $item) {
            $newOrder = $data['order'] + $i;
            $issueRepository->moveIssue($data, $item, $newOrder);
            $i++;
        }

        return response([
            'message' => 'issues has been moved successfully',
            'code' => 200
        ], 200);
    }
}
