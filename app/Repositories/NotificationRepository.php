<?php

namespace App\Repositories;

use App\Jobs\PushNotificationJob;
use App\Models\Issue;
use App\Models\Notification;
use App\Models\Project;
use App\Models\Recipient;
use App\Models\Team;
use App\Models\User;

class NotificationRepository
{
    public function getNotificationById($notifyId)
    {
        return Notification::query()
            ->where('id', '=', $notifyId)
            ->first();
    }

    public function getAllNotificationToThisUser($userId)
    {
        return Recipient::query()
            ->where('user_id', '=', $userId)
            ->with([
                'notification.role',
                'notification.sprint',
                'notification.issue.status',
                'notification.user',
                'notification.invitation',
                'notification.project'])
            ->latest('id')
            ->get();
    }

    public function getRecipient($data)
    {
        return Recipient::query()
            ->where('user_id', '=', $data['user_id'])
            ->where('notify_id', '=', $data['notify_id'])
            ->first();
    }

    public function getRecipientById($recipientId)
    {
        return Recipient::query()
            ->where('id', '=', $recipientId)
            ->first();
    }

    public function deleteNotification($notificationReceived)
    {
        $notificationReceived->delete();
    }

    public function storeRoleNotification($data, $user, $type, $title, $action, $content, $roleId)
    {
        $projectIdentify = $data['project_identify'];
        $url = "https://www.taskat.approved.tech/projects/$projectIdentify/role";
        $notification = Notification::query()
            ->create([
                'type' => $type,
                'title' => $title,
                'action' => $action,
                'content' => $content ?? null,
                'role_id' => $roleId,
                'project_id' => $data['project_id'],
                'user_id' => $data['user_id']
            ]);
        $this->storeRecipient($notification->id, $user->id);
        PushNotificationJob::dispatch($user, $title, $content, $url);
    }

    public function storeIssueNotification($data, $type, $title, $issueId, $action, $content)
    {
        return Notification::query()
            ->create([
                'type' => $type,
                'title' => $title,
                'action' => $action,
                'content' => $content ?? null,
                'issue_id' => $issueId,
                'project_id' => $data['project_id'],
                'user_id' => $data['user_id']
            ]);
    }

    public function storeSprintNotification($data, $type, $title, $action, $content)
    {
        return Notification::query()
            ->create([
                'type' => $type,
                'title' => $title,
                'action' => $action,
                'content' => $content ?? null,
                'sprint_id' => $data['sprint_id'],
                'project_id' => $data['project_id'],
                'user_id' => $data['user_id']
            ]);
    }

    public function storeInvitationNotification($data, $type, $title, $action, $content, $invitationId)
    {
        return Notification::query()
            ->create([
                'type' => $type,
                'title' => $title,
                'action' => $action,
                'invitation_id' => $invitationId,
                'content' => $content ?? null,
                'project_id' => $data['project_id'],
                'user_id' => $data['user_id']
            ]);
    }

    public function storeRecipient($notifyId, $userId)
    {
        Recipient::query()
            ->create([
                'notify_id' => $notifyId,
                'user_id' => $userId
            ]);
    }

    public function getUserById($userId)
    {
        return User::query()
            ->where('id', '=', $userId)
            ->first();
    }

    public function getProjectOwner($data)
    {
        $project = Project::query()
            ->find($data['project_id']);
        return $project->user_id;
    }

    public function getTeamMember($teamMember)
    {
        return Team::query()
            ->where('id', '=', $teamMember)
            ->first();
    }

    public function hadlePushNotofictionForIssue($data, $type, $title, $issueId, $action, $content)
    {
        $projectIdentify = $data['project_identify'];
        $url = "https://www.taskat.approved.tech/projects/$projectIdentify/backlog/?issue_details=$issueId";
        $issue = Issue::query()
            ->find($data['issue_id']);
        if ($issue->assign_to != null) {
            $teamMember = $this->getTeamMember($issue->assign_to);
            $user = $this->getUserById($teamMember->user_id);
        }
        if ($issue->assign_to != null && $data['user_id'] != $user->id) {
            $notification = $this->storeIssueNotification($data, $type, $title, $issueId, $action, $content);
            $this->storeRecipient($notification->id, $user->id);
            PushNotificationJob::dispatch($user, $title, $content, $url);
        }
    }

    public function hadlePushNotofictionForStatusIssue($data, $type, $title, $issueId, $action, $content)
    {
        $projectIdentify = $data['project_identify'];
        $url = "https://www.taskat.approved.tech/projects/$projectIdentify/backlog/?issue_details=$issueId";
        $issue = Issue::query()
            ->find($data['issue_id']);

        $reporter = $issue->user_id;
        $user = $this->getUserById($reporter);
        if ($data['user_id'] != $reporter && $user->player_ids != null) {
            $notification = $this->storeIssueNotification($data, $type, $title, $issueId, $action, $content);
            $this->storeRecipient($notification->id, $reporter);
            PushNotificationJob::dispatch($user, $title, $content, $url);
        }
    }
}
