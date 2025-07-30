<?php

namespace App\Http\Controllers\Issue;

use App\Http\Controllers\Controller;
use App\Http\Requests\Issue\DeleteBulkOfIssuesRequest;
use App\Http\Requests\Issue\UpdateBulkOfIssuesRequest;
use App\Models\Issue;
use App\Repositories\IssueRepository;
use Illuminate\Support\Facades\DB;

class BulkOfIssuesController extends Controller
{
    public function update(UpdateBulkOfIssuesRequest $request,
                           IssueRepository           $issueRepository,
                           Issue                     $issue)
    {
        $data = $request->validated();
        $this->authorize('update', [$issue, $data['project_id']]);

        $issuesId = $data['issues_id'];
        DB::beginTransaction();
        try {
            if (isset($data['assign_to'])) {
                foreach ($issuesId as $issue_id) {
                    $issueRepository->updateIssueAssign($data, $issue_id);
                }
            }
            if (isset($data['status_id'])) {
                foreach ($issuesId as $issue_id) {
                    $issueRepository->updateIssueStatus($data, $issue_id);
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return response([
            'message' => 'issues Updated successfully',
            'code' => 200
        ], 200);
    }

    public function destroy(DeleteBulkOfIssuesRequest $request,
                            IssueRepository           $issueRepository,
                            Issue                     $issue)
    {
        $data = $request->validated();
        $this->authorize('delete', [$issue, $data['project_id']]);

        $issuesId = $data['issues_id'];
        DB::beginTransaction();
        try {
            foreach ($issuesId as $issue_id) {
                $issueRepository->deleteTheIssue($data, $issue_id);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
        return response([
            'message' => 'issues deleted successfully',
            'code' => 200
        ], 200);
    }
}
