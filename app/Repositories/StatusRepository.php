<?php

namespace App\Repositories;

use App\Models\Issue;
use App\Models\ProjectHistory;
use App\Models\Sprint;
use App\Models\Status;
use App\Models\Team;
use Illuminate\Support\Facades\DB;

class StatusRepository
{
    public function listOfStatusesInProject($projectId)
    {
        return Status::query()
            ->where('project_id', '=', $projectId)
            ->orderBy('order', 'ASC')
            ->get();
    }

    public function filterBoard($data)
    {
        $issues = Issue::query()
            ->where('project_id', '=', $data['project_id'])
            ->whereNull('parent_id')
            ->orderBy('order_by_status');

        $statusIdFilter = $data['status'];
        $searchFilter = $data['search'];
        $assignToTeamMemberFilter = $data['assignee'];
        $sprintFilter = $data['sprint'];
        $labelFilter= $data['label']?? null;


        if ($searchFilter != null) {
            $issues->where('title', 'like', '%' . $searchFilter . '%');
        }
        if ($sprintFilter != null) {
            $issues->where('sprint_id', '=', $sprintFilter);
        }
        if ($statusIdFilter != null) {
            $issues->where('status_id', '=', $statusIdFilter);
        }
        if ($labelFilter != null) {
            $issues->whereHas('issueLabels', function ($query) use ($labelFilter) {
                $query->whereHas('label', function ($innerQuery) use ($labelFilter) {
                    $innerQuery->where('name', '=', $labelFilter);
                });
            });
        }
        if ($assignToTeamMemberFilter != null) {
            if ($assignToTeamMemberFilter == 0) {
                $issues->where('assign_to', '=', null);
            } else {
                $issues->where('assign_to', '=', $assignToTeamMemberFilter);
            }
        }
        return $issues->get();
    }

    public function getSprintsIsOpen($projectId)
    {
        return Sprint::query()
            ->where('project_id', '=', $projectId)
            ->whereNot('is_open', '=', 1)
            ->get();
    }

    public function listOfStatusesWithIssue($projectId, $data)
    {
        $sprintsIdOpen = $this->getSprintInTheProject($projectId)
            ->pluck('id')
            ->toArray();

        $filteredIssues = $this->filterBoard($data)
            ->whereIn('sprint_id', $sprintsIdOpen)
            ->pluck('id')
            ->toArray();

        return Status::query()
            ->where('project_id', '=', $projectId)
            ->with(['issues' => function ($query) use ($filteredIssues) {
                $query->whereIn('id', $filteredIssues);
            }, 'issues.teamMember.user', 'issues.user'])
            ->orderBy('order', 'ASC')
            ->get();
    }

    public function getSprintInTheProject($projectId)
    {
        return Sprint::query()
            ->where('project_id', '=', $projectId)
            ->where('is_open', '=', 1)
            ->get();
    }

    public function getAllProjectsTheUserBelongsIt($userId)
    {
        return Team::query()
            ->where('user_id', '=', $userId)
            ->get();
    }

    public function getAllStatusesOfAllProjects($userId)
    {
        $project = $this->getAllProjectsTheUserBelongsIt($userId);
        $bulkOfProject = $project->pluck('project_id')->toArray();

        return Status::query()
            ->whereIn('project_id', $bulkOfProject)
            ->orderBy('project_id', 'ASC')
            ->orderBy('order', 'ASC')
            ->pluck('name')
            ->unique()
            ->values()
            ->toArray();
    }

    public function getStatusById($data)
    {
        return Status::query()
            ->where('id', '=', $data['status_id'])
            ->where('project_id', '=', $data['project_id'])
            ->first();
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

    public function storeStatusNameInProjectHistory($data, $status)
    {
        return ProjectHistory::query()
            ->create([
                'status' => 'Update status name',
                'type' => 'status',
                'action' => 'edit',
                'old_data' => $status->name,
                'new_data' => $data['name'],
                'status_received_action' => $data['status_id'],
                'user_id' => $data['user_id'],
                'project_id' => $data['project_id'],
            ]);
    }

    public function updateStatus($data)
    {
        DB::beginTransaction();
        try {
            $status = $this->getStatusById($data);
            if ($data['name'] != $status->name) {
                $this->storeStatusNameInProjectHistory($data, $status);
            }
            $status->update([
                'name' => $data['name'],
                'max' => $data['max']
            ]);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
        return response([
            'message' => 'Status updated successfully',
            'code' => 200
        ], 200);
    }

    public function moveColumnStatus($data)
    {
        DB::beginTransaction();
        try {
            $status = $this->getStatusById($data);
            $this->reorderStatusColumn($data, $status->order);
            $status->update([
                'order' => $data['order']
            ]);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return response([
            'message' => 'Status moved successfully',
            'code' => 200
        ], 200);
    }

    public function reorderStatusColumn($data, $oldOder)
    {
        Status::query()
            ->where('project_id', '=', $data['project_id'])
            ->where('order', '>', $oldOder)
            ->decrement('order');

        Status::query()
            ->where('project_id', '=', $data['project_id'])
            ->where('order', '>=', $data['order'])
            ->increment('order');
    }

    public function storeNewStatusInProjectHistory($data, $statusId)
    {
        ProjectHistory::query()
            ->create([
                'status' => 'Create the Status',
                'type' => 'status',
                'action' => 'create',
                'status_received_action' => $statusId,
                'project_id' => $data['project_id'],
                'user_id' => $data['user_id'],
            ]);
    }

    public function createNewStatus($data)
    {
        $this->reorderStatusColumn($data, 10000);
        $status = Status::query()
            ->create([
                'name' => $data['name'],
                'type' => 'In Progress',
                'order' => $data['order'],
                'project_id' => $data['project_id'],
                'user_id' => $data['user_id'],
            ]);
        $this->storeNewStatusInProjectHistory($data, $status->id);
    }

    public function countOfStatus($data, $status)
    {
        return Status::query()
            ->where('project_id', '=', $data['project_id'])
            ->where('type', '=', $status->type)
            ->count();
    }

    public function deleteStatus($data, $status)
    {
        $this->reorderStatusColumn($data, $status->order);
        $status->update([
            'deleted_at' => now(),
            'order' => $data['order']
        ]);
        $this->actionsThatOccurWhenStatusIsDeleted($data);
    }

    public function storeStatusDeletedInProjectHistory($data, $statusId)
    {
        ProjectHistory::query()
            ->create([
                'status' => 'Deleted the Status',
                'type' => 'status',
                'action' => 'delete',
                'status_received_action' => $statusId,
                'project_id' => $data['project_id'],
                'user_id' => $data['user_id'],
            ]);
    }

    public function actionsThatOccurWhenStatusIsDeleted($data)
    {
        $maxOrder = Issue::query()
            ->where('status_id', '=', $data['issues_status_id'])
            ->max('order_by_status');

        $issues = Issue::query()
            ->where('status_id', '=', $data['status_id'])
            ->pluck('id')
            ->toArray();
        $maxOrder++;
        foreach ($issues as $issue) {
            Issue::query()
                ->where('id', '=', $issue)
                ->update([
                    'status_id' => $data['issues_status_id'],
                    'order_by_status' => $maxOrder,
                ]);
            $maxOrder++;
        }
    }

    public function checkNameStatusIsUnique($data)
    {

        $statusesName = Status::query()
            ->where('project_id', '=', $data['project_id'])
            ->get()
            ->pluck('name')
            ->toArray();

        $name = strtoupper($data['name']);
        return in_array($name, $statusesName);
    }
}
