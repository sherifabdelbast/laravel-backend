<?php

namespace App\Http\Controllers\Sprint;

use App\Http\Controllers\Controller;
use App\Http\Requests\Sprint\StoreSprintBulkIssuesRequest;
use App\Models\Sprint;
use App\Repositories\IssueRepository;
use App\Repositories\ProjectRepository;
use App\Repositories\SprintRepository;
use Illuminate\Support\Facades\DB;

class CreateSprintWithBulkIssues extends Controller
{

    public function store(StoreSprintBulkIssuesRequest $request,
                          SprintRepository             $sprintRepository,
                          IssueRepository              $issueRepository,
                          Sprint                       $sprint)
    {
        $data = $request->validated();

        $this->authorize('create', [$sprint, $data['project_id']]);
        DB::beginTransaction();
        try {
            $sprint = $sprintRepository->storeNewSprint($data);
            $data['sprint_id'] = $sprint->id;

            $issues = $data['issues_id'];
            $issuesReverse = array_reverse($issues);

            foreach ($issuesReverse as $issue) {
                $order = null + 1;
                $issueRepository->moveIssue($data, $issue, $order);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return response([
            'message' => 'Sprint created successfully',
            'code' => 200
        ], 200);
    }
}
