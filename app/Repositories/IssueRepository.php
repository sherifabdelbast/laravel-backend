<?php

namespace App\Repositories;

use App\Models\Issue;
use App\Models\IssueFiles;
use App\Models\IssueHistory;
use App\Models\IssueLabel;
use App\Models\Label;
use App\Models\Notification;
use App\Models\Project;
use App\Models\ProjectHistory;
use App\Models\Recipient;
use App\Models\Sprint;
use App\Models\Status;
use App\Models\Team;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

##TODO::Must Refactor(Issue History)
class IssueRepository
{
    public function getIssueById($data)
    {
        return Issue::query()
            ->id($data['issue_id'])
            ->project($data['project_id'])
            ->first();
    }

    public function checkIssueExists($projectId, $issueId)
    {
        return Issue::query()
            ->id($issueId)
            ->project($projectId)
            ->with(['project', 'status', 'teamMember.user', 'user', 'sprint',
                'issueFiles', 'subIssues.teamMember.user', 'subIssues.status'])
            ->first();
    }

    public function showIssue($projectId, $issueId)
    {
        $issue = Issue::query()
            ->id($issueId)
            ->project($projectId)
            ->with(['project', 'status', 'teamMember.user', 'user', 'sprint',
                'issueFiles', 'subIssues.teamMember.user', 'subIssues.status', 'issueLabels.label'])
            ->withCount('subIssues')
            ->first();
        $labelNames = $issue->issueLabels->pluck('label.name')->toArray();
        unset($issue->issueLabels);
        $issue->labelNames = $labelNames;

        return $issue;
    }

    public function deleteTheIssue($data, $issueId)
    {
        $issue = $this->checkIssueExists($data['project_id'], $issueId);


        $this->storeDeleteInProjectHistory($data, $issueId);
        $issue->delete();
    }

    public function storeDeleteInProjectHistory($data, $issueId)
    {
        $projectId = $data['project_id'];
        ProjectHistory::query()
            ->create([
                'status' => 'Deleted the issue',
                'type' => 'Issue',
                'action' => 'delete',
                'issue_id' => $issueId,
                'project_id' => $projectId,
                'user_id' => $data['user_id'],

            ]);
    }

    public function uploadIssueFiles($data, $issueFile)
    {
        $uploadFile = Str::random(10) . '-' . $issueFile->hashName();
        Storage::disk('public')->put('upload/issues_files/' . $uploadFile, file_get_contents($issueFile));
        $type = $issueFile->getMimeType();
        $size = $issueFile->getSize();
        $originalFileName = $issueFile->getClientOriginalName();
        return IssueFiles::query()
            ->create([
                'files' => $uploadFile,
                'issue_id' => $data['issue_id'],
                'type' => $type,
                'name' => $originalFileName,
                'size' => $size,
            ]);
    }

    public function checkLabelInProject($data)
    {
        return Label::query()
            ->where('project_id', '=', $data['project_id'])
            ->where('name', '=', $data['label'])
            ->exists();
    }

    public function createLabel($data)
    {
        $checkLabel = $this->checkLabelInProject($data);
        if (!$checkLabel) {
            $label = Label::query()
                ->create([
                    'name' => $data['label'],
                    'project_id' => $data['project_id']
                ]);
            IssueLabel::query()
                ->create([
                    'issue_id' => $data['issue_id'],
                    'label_id' => $label->id
                ]);
        } else {
            $existingLabel = Label::query()
                ->where('name', $data['label'])
                ->where('project_id', $data['project_id'])
                ->first();

            $existingLocal = IssueLabel::query()
                ->where('issue_id', '=', $data['issue_id'])
                ->where('label_id', '=', $existingLabel->id)
                ->exists();

            if (!$existingLocal) {
                IssueLabel::query()
                    ->create([
                        'issue_id' => $data['issue_id'],
                        'label_id' => $existingLabel->id
                    ]);
            }

        }
    }


    public function storeCreateLabelInProjectHistory($data, $labelId)
    {
        $projectId = $data['project_id'];
        ProjectHistory::query()
            ->create([
                'status' => 'Create the label',
                'type' => 'Label',
                'action' => 'create',
                'label_received_action' => $labelId,
                'project_id' => $projectId,
                'user_id' => $data['user_id'],

            ]);
    }

    public function deleteLabel($data)
    {
        $label = Label::query()
            ->where('name', $data['label'])
            ->where('project_id', $data['project_id'])
            ->first();
        if ($label) {
            return IssueLabel::query()
                ->where('label_id', $label->id)
                ->where('issue_id', $data['issue_id'])
                ->delete();
        }
        return $label;
    }

    public function listOfLabel($data)
    {
        return Label::query()
            ->where('project_id', '=', $data['project_id'])
            ->withCount('issueLabels as issue_count')
            ->orderByDesc('issue_count')
            ->pluck('name');
    }

    public function deleteIssueFile($fileId)
    {
        return IssueFiles::query()
            ->where('id', '=', $fileId)
            ->delete();
    }

    public function updateIssueTitle($data)
    {
        $this->storeTitleInIssueHistory($data);
        $issueId = $data['issue_id'];
        $projectId = $data['project_id'];
        $issue = $this->checkIssueExists($projectId, $issueId);
        return $issue->update([
            'title' => $data['title']
        ]);
    }


    public function storeTitleInIssueHistory($data)
    {
        $issueId = $data['issue_id'];
        $projectId = $data['project_id'];
        $issue = $this->checkIssueExists($projectId, $issueId);
        return IssueHistory::query()
            ->create([
                'status' => 'updated the title',
                'type' => 'title',
                'old_data' => $issue->title,
                'new_data' => $data['title'],
                'issue_id' => $issueId,
                'user_id' => $data['user_id'],
            ]);
    }

    public function updateIssueType($data)
    {
        $this->storeTypeInIssueHistory($data);
        $issueId = $data['issue_id'];
        $projectId = $data['project_id'];
        $issue = $this->checkIssueExists($projectId, $issueId);
        return $issue->update([
            'type' => $data['type']
        ]);
    }

    public function storeSprintInIssueHistory($data)
    {
        $sprint = Sprint::query()
            ->where('project_id', '=', $data['project_id'])
            ->where('id', '=', $data['sprint_id'])
            ->first();

        $issueId = $data['issue_id'];
        $projectId = $data['project_id'];
        $issue = $this->checkIssueExists($projectId, $issueId);

        return IssueHistory::query()
            ->create([
                'status' => 'updated the title',
                'type' => 'title',
                'old_data' => $issue->sprint->name ?? null,
                'new_data' => $sprint->name ?? null,
                'issue_id' => $issueId,
                'user_id' => $data['user_id'],
            ]);
    }

    public function maxOrderBySprint($sprintId)
    {
        return Issue::query()
            ->where('sprint_id', '=', $sprintId)
            ->max('order');
    }

    public function updateIssueSprint($data)
    {
        $this->storeSprintInIssueHistory($data);
        $issueId = $data['issue_id'];
        $maxOrder = $this->maxOrderBySprint($data['sprint_id']);
        $this->moveIssue($data, $issueId, $maxOrder + 1);
    }

    public function storeTypeInIssueHistory($data)
    {
        $issueId = $data['issue_id'];
        $projectId = $data['project_id'];
        $issue = $this->checkIssueExists($projectId, $issueId);
        return IssueHistory::query()
            ->create([
                'status' => 'updated the Type',
                'type' => 'type',
                'old_data' => $issue->type,
                'new_data' => $data['type'],
                'issue_id' => $issueId,
                'user_id' => $data['user_id'],
            ]);
    }

    public function updateIssueDescription($data)
    {
        $this->storeDescriptionInIssueHistory($data);
        $issueId = $data['issue_id'];
        $projectId = $data['project_id'];
        $issue = $this->checkIssueExists($projectId, $issueId);

        return $issue->update([
            'description' => $data['description'],
            'mention_list' => $data['mentionList'] ?? null
        ]);
    }

    public function mention($oldMentions, $newMentions)
    {   if($oldMentions == null){

        $oldMentions=[];
    }
        return array_diff($newMentions, $oldMentions);
    }

    public function storeDescriptionInIssueHistory($data)
    {
        $issueId = $data['issue_id'];
        $projectId = $data['project_id'];
        $issue = $this->checkIssueExists($projectId, $issueId);
        return IssueHistory::query()
            ->create([
                    'status' => 'updated the Description',
                    'type' => 'description',
                    'old_data' => $issue->description,
                    'new_data' => $data['description'],
                    'issue_id' => $issueId,
                    'user_id' => $data['user_id'],
                ]
            );
    }

    public function updateIssueAssign($data, $issueId)
    {
        $this->storeAssignInIssueHistory($data, $issueId);
        $projectId = $data['project_id'];
        $issue = $this->checkIssueExists($projectId, $issueId);
        return $issue->update(
            [
                'assign_to' => $data['assign_to']
            ]);
    }

    public function storeAssignInIssueHistory($data, $issueId)
    {

        $projectId = $data['project_id'];
        $issue = $this->checkIssueExists($projectId, $issueId);
        return IssueHistory::query()
            ->create([
                    'status' => 'changed the Assignee',
                    'type' => 'assignee',
                    'assign_from' => $issue->assign_to,
                    'assign_to' => $data['assign_to'],
                    'issue_id' => $issueId,
                    'user_id' => $data['user_id'],
                ]
            );
    }

    public function rearrangeStatusToReceiveIssueWhenUpdateTheStatus($data, $issue)
    {
        Issue::query()
            ->where('project_id', '=', $data['project_id'])
            ->where('status_id', '=', $issue->status_id)
            ->where('order_by_status', '>', $issue->order_by_status)
            ->decrement('order_by_status');

        return Issue::query()
            ->where('project_id', '=', $data['project_id'])
            ->where('status_id', '=', $data['status_id'])
            ->max('order_by_status');
    }

    public function updateIssueStatus($data, $issueId)
    {
        $issue = $this->checkIssueExists($data['project_id'], $issueId);
        $newOrderByStatus = $this->rearrangeStatusToReceiveIssueWhenUpdateTheStatus($data, $issue);
        $this->storeStatusInIssueHistory($data, $issue);
        return $issue->update([
            'status_id' => $data['status_id'],
            'order_by_status' => $newOrderByStatus + 1
        ]);
    }

    public function storeStatusInIssueHistory($data, $issue)
    {
        return IssueHistory::query()
            ->create(
                [
                    'status' => 'changed the Status',
                    'type' => 'status',
                    'old_data' => $this->getStatusName($issue->status_id),
                    'new_data' => $this->getStatusName($data['status_id']),
                    'issue_id' => $issue->id,
                    'user_id' => $data['user_id'],
                ]
            );
    }

    public function getStatusName($statusId)
    {
        $status = Status::query()
            ->where('id', '=', $statusId)
            ->first();
        return $status->name;
    }

    public function storeEstimatedAtInIssueHistory($data)
    {
        $issueId = $data['issue_id'];
        $projectId = $data['project_id'];
        $issue = $this->checkIssueExists($projectId, $issueId);
        return IssueHistory::query()
            ->create(
                [
                    'status' => 'updated the estimate Time',
                    'type' => 'estimated_at',
                    'old_estimated_at' => $issue->estimated_at,
                    'new_estimated_at' => $data['estimated_at'],
                    'issue_id' => $issueId,
                    'user_id' => $data['user_id'],
                ]
            );
    }

    public function updateIssueEstimatedAt($data)
    {
        $this->storeEstimatedAtInIssueHistory($data);
        $issueId = $data['issue_id'];
        $projectId = $data['project_id'];
        $issue = $this->checkIssueExists($projectId, $issueId);
        return $issue->update(
            [
                'estimated_at' => $data['estimated_at']
            ]);
    }

    public function getStatusDefaultByProject($projectId)
    {
        return Status::query()
            ->where('project_id', '=', $projectId)
            ->first();
    }

    public function storeCreatedIssueInIssueHistory($issueId, $userId)
    {
        return IssueHistory::query()
            ->create([
                    'status' => 'created the Issue',
                    'type' => null,
                    'old_data' => null,
                    'new_data' => null,
                    'issue_id' => $issueId,
                    'user_id' => $userId,
                ]
            );
    }

    public function makeAOrderWhenCreateNewIssue($data, $statusId)
    {
        $lastOrderBySprint = Issue::query()
            ->where('project_id', '=', $data['project_id'])
            ->where('sprint_id', '=', $data['sprint_id'])
            ->max('order');

        $lastOrderByStatus = Issue::query()
            ->where('project_id', '=', $data['project_id'])
            ->where('status_id', '=', $statusId)
            ->max('order_by_status');

        return [$lastOrderBySprint, $lastOrderByStatus];
    }

    public function makeKeyToIssue($projectId)
    {
        $countOfIssues = Issue::query()
            ->where('project_id', '=', $projectId)
            ->count();
        $project = Project::query()
            ->where('id', '=', $projectId)
            ->first();

        return $project->key . '-' . $countOfIssues + 1;
    }

    public function createIssue($data)
    {
        DB::beginTransaction();
        try {
            $status = $this->getStatusDefaultByProject($data['project_id']);
            $lastOrder = $this->makeAOrderWhenCreateNewIssue($data, $status->id);
            $key = $this->makeKeyToIssue($data['project_id']);
            $issue = Issue::query()
                ->create([
                    'title' => $data['title'],
                    'key' => $key,
                    'type' => $data['type'],
                    'status_id' => $status->id,
                    'sprint_id' => $data['sprint_id'],
                    'project_id' => $data['project_id'],
                    'parent_id' => $data['parent_id'] ?? null,
                    'user_id' => $data['user_id'],
                    'order' => $lastOrder[0] + 1,
                    'order_by_status' => $lastOrder[1] + 1
                ]);
            $this->storeCreatedIssueInIssueHistory($issue->id, $data['user_id']);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
        return $issue;
    }

    public function getProject($userId)
    {
        $team = Team::query()
            ->where('user_id', '=', $userId)
            ->get();
        return $team->pluck('project_id')->toArray();
    }

    public function getTeamMemberByUserId($userId)
    {
        return Team::query()
            ->where('user_id', '=', $userId)
            ->get();
    }

    public function getAllIssues($data)
    {
        $userId = $data['user_id'];
        $projectIdFilter = $data['project_id'];
        $statusNameFilter = $data['status'];
        $assignToMeFilter = $data['assignToMe'];
        $searchFilter = $data['search'];
        $assignToTeamMemberFilter = $data['assignee'];

        $project = $this->getProject($userId);
        $allIssues = Issue::query()
            ->whereIn('project_id', $project)
            ->whereNull('parent_id')
            ->with(['project', 'status', 'teamMember.user']);

        if ($searchFilter != null) {
            $allIssues->where('title', 'like', '%' . $searchFilter . '%');
        }
        if ($projectIdFilter != null) {
            $allIssues->where('project_id', '=', $projectIdFilter);
        }
        if ($statusNameFilter != null) {
            $statuesId = Status::query()
                ->whereIn('project_id', $project)
                ->where('name', '=', $statusNameFilter)
                ->pluck('id')
                ->toArray();
            $allIssues->whereIn('status_id', $statuesId);
        }
        if ($assignToMeFilter != null) {
            $teamMember = $this->getTeamMemberByUserId($userId);
            $bulkTeamMembersId = $teamMember->pluck('id')->toArray();
            $allIssues->whereIn('assign_to', $bulkTeamMembersId);
        }
        if ($assignToTeamMemberFilter != null) {
            if ($assignToTeamMemberFilter == 0) {
                $allIssues->where('assign_to', '=', null);
            } else {
                $teamMember = $this->getTeamMemberByUserId($assignToTeamMemberFilter);
                $bulkTeamMembersId = $teamMember->pluck('id')->toArray();
                $allIssues->whereIn('assign_to', $bulkTeamMembersId);
            }
        }
        return $allIssues->get();
    }

    public function getIssuesByAssignee($assigneeId)
    {
        return Issue::query()
            ->where('assign_to', '=', $assigneeId)
            ->get();
    }

    public function filterBacklog($data)
    {
        $issues = Issue::query()
            ->project($data['project_id'])
            ->orderBy('order')
            ->with(['teamMember.user', 'status']);

        $statusIdFilter = $data['status'];
        $searchFilter = $data['search'];
        $assignToTeamMemberFilter = $data['assignee'];
        $labelFilter= $data['label']?? null;

        if ($searchFilter != null) {
            $issues->where('title', 'like', '%' . $searchFilter . '%');
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

    public function getAllIssuesInThisProjectBelongToSprints($data)
    {
        $issues = $this->filterBacklog($data)
            ->whereNotNull('sprint_id')
            ->whereNull('parent_id');

        $filteredIssues = $issues->pluck('id')
            ->toArray();

        $sprints = Sprint::query()
            ->where('project_id', '=', $data['project_id'])
            ->with(['issues' => function ($query) use ($filteredIssues) {
                $query->whereIn('id', $filteredIssues);
            }, 'issues.status', 'issues.teamMember.user'])
            ->get()->values();

        return $sprints->map(function ($sprint) {
            $totalEstimatedTime = $this->totalEstimatedTime($sprint->issues);
            $sprint['totalEstimatedTime'] = $totalEstimatedTime;
            return $sprint;
        });
    }

    public function getAllIssuesInThisProjectNotBelongToSprints($data)
    {

        $issuesInBackLog = $this->filterBacklog($data);
        return $issuesInBackLog
            ->whereNull('sprint_id')
            ->whereNull('parent_id')->values();
    }

    public function totalEstimatedTime($issues)
    {
        $totalTime = [0, 0, 0];

        foreach ($issues as $issue) {
            $estimatedTime = $issue->estimated_at;
            $totalTime[2] += $estimatedTime[2];
            $totalTime[1] += $estimatedTime[1] + (int)($totalTime[2] / 60);
            $totalTime[2] %= 60;
            $totalTime[0] += $estimatedTime[0] + (int)($totalTime[1] / 24);
            $totalTime[1] %= 24;
        }
        return $totalTime;
    }

    public function rearrangeSprintToReceiveIssue($data, $issue, $newOrder)
    {
        Issue::query()
            ->where('project_id', '=', $data['project_id'])
            ->where('sprint_id', '=', $issue->sprint_id)
            ->where('order', '>', $issue->order)
            ->decrement('order');

        Issue::query()
            ->where('project_id', '=', $data['project_id'])
            ->where('sprint_id', '=', $data['sprint_id'])
            ->where('order', '>=', $newOrder)
            ->increment('order');
    }

    public function moveIssue($data, $issueId, $newOrder)
    {
        DB::beginTransaction();
        try {
            $issue = $this->checkIssueExists($data['project_id'], $issueId);
            $this->rearrangeSprintToReceiveIssue($data, $issue, $newOrder);
            $issue->update([
                'sprint_id' => $data['sprint_id'],
                'order' => $newOrder
            ]);
            DB::commit();
        } catch (\Exception $e) {

            DB::rollBack();
            throw $e;
        }
    }

    public function getIssueHistory($data)
    {
        $issueId = $data['issue_id'];
        $totalRecords = IssueHistory::query()
            ->where('issue_id', '=', $issueId)
            ->count();

        $currentPage = $data['page'];

        $totalPages = ceil($totalRecords / 10);

        $pageHistory = IssueHistory::query()
            ->where('issue_id', '=', $issueId)
            ->with(['user', 'assignFromTeamMember.user', 'assignToTeamMember.user'])
            ->latest('id')
            ->take(10 * $currentPage)
            ->get();

        return ['issue_history' => $pageHistory,
            'total_pages' => $totalPages,
            'current_page' => $currentPage];
    }

    public
    function checkIfTeamMemberExists($data)
    {
        return Team::query()
            ->where('id', '=', $data['assign_to'])
            ->where('project_id', '=', $data['project_id'])
            ->where('access', '=', 1)
            ->first();
    }

    public
    function checkIfStatusExists($data)
    {
        return Status::query()
            ->where('id', '=', $data['status_id'])
            ->where('project_id', '=', $data['project_id'])
            ->first();
    }

    public function rearrangeStatusToReceiveIssue($data, $issue)
    {
        Issue::query()
            ->where('project_id', '=', $data['project_id'])
            ->where('status_id', '=', $issue->status_id)
            ->where('order_by_status', '>', $issue->order_by_status)
            ->decrement('order_by_status');

        Issue::query()
            ->where('project_id', '=', $data['project_id'])
            ->where('status_id', '=', $data['status_id'])
            ->where('order_by_status', '>=', $data['order_by_status'])
            ->increment('order_by_status');
    }

    public function moveIssueByStatus($data)
    {
        DB::beginTransaction();
        try {
            $issue = $this->checkIssueExists($data['project_id'], $data['issue_id']);
            $this->rearrangeStatusToReceiveIssue($data, $issue);
            $this->storeStatusInIssueHistory($data, $issue);
            $issue->update([
                'status_id' => $data['status_id'],
                'order_by_status' => $data['order_by_status']
            ]);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function getTypeIssue($projectId, $issueId)
    {
        return Issue::query()
            ->where('id', '=', $issueId)
            ->where('project_id', '=', $projectId)
            ->get()
            ->pluck('type')
            ->toArray();
    }

    public function copyIssue($data, $issue)
    {
        DB::beginTransaction();
        try {
            $newIssue = $issue->replicate();
            $newIssue->order = 100000;
            $newIssue->order_by_status = 100000;
            $key = $this->makeKeyToIssue($data['project_id']);

            $newIssue->key = $key;
            $data['sprint_id'] = $issue->sprint_id;
            $data['status_id'] = $issue->status_id;
            $data['order_by_status'] = $issue->order_by_status + 1;
            $newIssue->save();

            $this->storeCreatedIssueInIssueHistory($newIssue->id, $data['user_id']);
            $this->moveIssue($data, $newIssue->id, $issue->order + 1);
            $this->rearrangeStatusToReceiveIssue($data, $newIssue);

            $newIssue->order_by_status = $data['order_by_status'];
            $newIssue->save();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updatePriority($data, $issue)
    {
        $issue->update([
            'priority' => $data['priority']
        ]);
    }

    public function storePriorityInIssueHistory($data, $issue)
    {
        IssueHistory::query()
            ->create([
                'status' => 'updated the Priority',
                'type' => 'Priority',
                'old_data' => $issue->priority,
                'new_data' => $data['priority'],
                'issue_id' => $issue->id,
                'user_id' => $data['user_id'],
            ]);
    }
}
