<?php

namespace App\Http\Controllers\Issue;

use App\Http\Controllers\Controller;
use App\Http\Requests\Issue\UpdatePriorityIssueRequest;
use App\Models\Issue;
use App\Repositories\IssueRepository;
use Illuminate\Support\Facades\DB;

class UpdatePriorityIssueController extends Controller
{
    public function update(UpdatePriorityIssueRequest $request,
                           IssueRepository            $issueRepository,
                           Issue                      $issue)
    {
        $data = $request->validated();
        $this->authorize('update', [$issue, $data['project_id']]);

        DB::beginTransaction();
        try {
            $issue = $issueRepository->getIssueById($data);
            $issueRepository->storePriorityInIssueHistory($data, $issue);
            $issueRepository->updatePriority($data, $issue);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return response([
            'message' => 'Issue updated successfully.',
            'code' => 200
        ], 200);
    }
}
