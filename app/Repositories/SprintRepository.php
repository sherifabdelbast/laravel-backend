<?php

namespace App\Repositories;

use App\Models\Issue;
use App\Models\ProjectHistory;
use App\Models\Sprint;
use App\Models\Status;
use Exception;
use Illuminate\Support\Facades\DB;

class SprintRepository
{
    public function getBySprintId($sprintId)
    {
        return Sprint::query()
            ->where('id', '=', $sprintId)
            ->first();
    }

    public function editSprint($data, $sprintId)
    {
        $sprint = $this->getBySprintId($sprintId);
        if ($data['name'] != $sprint->name) {
            $this->storeUpdateNameSprintInProjectHistory($data, $sprint->name);
        }
        if ($data['goal'] != $sprint->goal) {
            $this->storeUpdateGoalSprintInProjectHistory($data, $sprint->goal);
        }
        if ($data['start_date'] != $sprint->start_date) {
            $this->storeUpdateStartDateSprintInProjectHistory($data, $sprint->start_date);
        }
        if ($data['end_date'] != $sprint->end_date) {
            $this->storeUpdateEndDateSprintInProjectHistory($data, $sprint->end_date);
        }
        if ($data['duration'] != $sprint->duration) {
            $this->storeUpdateDurationSprintInProjectHistory($data, $sprint->duration);
        }
        $sprint->update([
            'name' => $data['name'],
            'goal' => $data['goal'],
            'start_date' => $data['start_date'],
            'duration' => $data['duration'],
            'end_date' => $data['end_date'],
        ]);
        return $sprint;
    }

    public function storeUpdateNameSprintInProjectHistory($data, $oldData)
    {
        return ProjectHistory::query()
            ->create([
                'status' => 'Update sprint name',
                'type' => 'sprint',
                'action' => 'edit',
                'old_data' => $oldData,
                'new_data' => $data['name'],
                'user_id' => $data['user_id'],
                'project_id' => $data['project_id'],
                'sprint_received_action' => $data['sprint_id']
            ]);
    }

    public function storeUpdateGoalSprintInProjectHistory($data, $oldData)
    {
        return ProjectHistory::query()
            ->create([
                'status' => 'Update sprint goal',
                'type' => 'sprint',
                'action' => 'edit',
                'old_data' => $oldData,
                'new_data' => $data['goal'],
                'user_id' => $data['user_id'],
                'project_id' => $data['project_id'],
                'sprint_received_action' => $data['sprint_id']
            ]);
    }

    public function storeUpdateStartDateSprintInProjectHistory($data, $oldData)
    {
        return ProjectHistory::query()
            ->create([
                'status' => 'Update sprint start date',
                'type' => 'sprint',
                'action' => 'edit',
                'old_data' => $oldData,
                'new_data' => $data['start_date'],
                'user_id' => $data['user_id'],
                'project_id' => $data['project_id'],
                'sprint_received_action' => $data['sprint_id']
            ]);
    }

    public function storeUpdateEndDateSprintInProjectHistory($data, $oldData)
    {
        return ProjectHistory::query()
            ->create([
                'status' => 'Update sprint end date',
                'type' => 'sprint',
                'action' => 'edit',
                'old_data' => $oldData,
                'new_data' => $data['end_date'],
                'user_id' => $data['user_id'],
                'project_id' => $data['project_id'],
                'sprint_received_action' => $data['sprint_id']
            ]);
    }

    public function storeUpdateDurationSprintInProjectHistory($data, $oldData)
    {
        return ProjectHistory::query()
            ->create([
                'status' => 'Update sprint duration',
                'type' => 'sprint',
                'action' => 'edit',
                'old_data' => $oldData,
                'new_data' => $data['duration'],
                'user_id' => $data['user_id'],
                'project_id' => $data['project_id'],
                'sprint_received_action' => $data['sprint_id']
            ]);
    }

    public function getAllSprintInThisProject($projectId)
    {
        return Sprint::query()
            ->where('project_id', '=', $projectId);
    }

    public function storeNewSprint($data)
    {
        $projectId = $data['project_id'];
        $countOfSprint = $this->getAllSprintInThisProject($projectId)
            ->get()
            ->count();
        $sprint = Sprint::query()
            ->create([
                'name' => 'sprint ' . $countOfSprint + 1,
                'project_id' => $projectId,
                'user_id' => $data['user_id']
            ]);
        $this->storeSprintInProjectHistory($data, $sprint->id);
        return $sprint;
    }

    public function storeSprintInProjectHistory($data, $sprintId)
    {
        return ProjectHistory::query()
            ->create([
                'status' => 'Create the sprint',
                'type' => 'sprint',
                'action' => 'create',
                'user_id' => $data['user_id'],
                'project_id' => $data['project_id'],
                'sprint_received_action' => $sprintId
            ]);
    }

    public function storeSprintDeletedInProjectHistory($data)
    {
        return ProjectHistory::query()
            ->create([
                'status' => 'Delete the sprint',
                'type' => 'sprint',
                'action' => 'delete',
                'user_id' => $data['user_id'],
                'project_id' => $data['project_id'],
                'sprint_received_action' => $data['sprint_id']
            ]);
    }

    /**
     * @throws Exception
     */
    public function startSprint($data)
    {
        DB::beginTransaction();
        try {
            $this->editSprint($data, $data['sprint_id']);
            $sprint = $this->getBySprintId($data['sprint_id']);
            $sprint->update([
                'is_open' => 1,
                'status' => 'IN PROGRESS'
            ]);
            $this->storeSprintStartInProjectHistory($data);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function storeSprintStartInProjectHistory($data)
    {
        return ProjectHistory::query()
            ->create([
                'status' => 'Sprint started',
                'type' => 'sprint',
                'action' => 'start',
                'user_id' => $data['user_id'],
                'project_id' => $data['project_id'],
                'sprint_received_action' => $data['sprint_id']
            ]);

    }

    public function getStatusesIdToThisProject($data)
    {
        $otherStatuses = Status::query()
            ->where('project_id', '=', $data['project_id'])
            ->whereNot('type', '=', 'Done')
            ->get()
            ->pluck('id')
            ->toArray();

        $DoneStatusId = Status::query()
            ->where('project_id', '=', $data['project_id'])
            ->where('type', '=', 'Done')
            ->get()
            ->pluck('id')
            ->toArray();

        return [$otherStatuses, $DoneStatusId];
    }

    public function completeSprint($data)
    {
        DB::beginTransaction();
        try {
            $sprint = $this->getBySprintId($data['sprint_id']);
            $sprint->update([
                'is_completed' => 1,
                'is_open' => 0,
                'status' => 'COMPLETED'
            ]);
            $statuses = $this->getStatusesIdToThisProject($data);
            $this->actionsThatOccurWhenCompleteSprint($data, $sprint, $statuses[0], $statuses[1]);
            $this->storeSprintCompleteInProjectHistory($data);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function storeSprintCompleteInProjectHistory($data)
    {
        return ProjectHistory::query()
            ->create([
                'status' => 'sprint Completed',
                'type' => 'sprint',
                'action' => 'complete',
                'user_id' => $data['user_id'],
                'project_id' => $data['project_id'],
                'sprint_received_action' => $data['sprint_id']
            ]);

    }


    public function actionsThatOccurWhenCompleteSprint($data, $sprint, $otherStatuses, $doneStatusId)
    {
        $maxOrder = Issue::query()
            ->where('sprint_id', '=', $data['issue_sprint_id'])
            ->max('order');
        $maxOrder++;

        $issues = Issue::query()
            ->where('sprint_id', '=', $sprint->id)
            ->whereIn('status_id', $otherStatuses)
            ->pluck('id')
            ->toArray();
        foreach ($issues as $issue) {
            Issue::query()
                ->where('id', '=', $issue)
                ->update([
                    'sprint_id' => $data['issue_sprint_id'],
                    'order' => $maxOrder
                ]);
            $maxOrder++;
        }

        Issue::query()
            ->where('sprint_id', '=', $sprint->id)
            ->whereIn('status_id', $doneStatusId)
            ->update([
                'is_completed' => 1
            ]);
    }

    public function actionsThatOccurWhenDeletedSprint($data)
    {
        $maxOrder = Issue::query()
            ->where('sprint_id', '=', $data['sprint_id'])
            ->max('order');
        $maxOrder++;

        $issues = Issue::query()
            ->where('project_id', '=', $data['project_id'])
            ->where('sprint_id', '=', $data['sprint_id'])
            ->pluck('id')
            ->toArray();

        foreach ($issues as $issue) {
            Issue::query()
                ->where('id', '=', $issue)
                ->update([
                    'sprint_id' => $data['issue_sprint_id'],
                    'order' => $maxOrder
                ]);
            $maxOrder++;
        }
    }
}
