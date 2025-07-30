<?php

namespace App\Http\Controllers\Issue;

use App\Http\Controllers\Controller;
use App\Http\Requests\Issue\ShowIssueRequest;
use App\Http\Requests\Issue\UpdateIssueRequest;
use App\Http\Requests\Issue\DeleteIssueRequest;
use App\Http\Requests\Issue\ListIssueRequest;
use App\Http\Requests\Issue\StoreIssueRequest;
use App\Jobs\PushNotificationJob;
use App\Models\Issue;
use App\Repositories\IssueRepository;
use App\Repositories\NotificationRepository;
use App\Repositories\TeamRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\DB;

class IssueController extends Controller
{
    // your work
    public function index(ListIssueRequest $request,
                          IssueRepository  $issueRepository)
    {
        $data = $request->validated();
        $allIssues = $issueRepository->getAllIssues($data);
        return response([
            'message' => 'Here are the issues relevant to you.',
            'code' => 200,
            'issues' => $allIssues
        ], 200);
    }

    public function store(StoreIssueRequest $request,
                          IssueRepository   $issueRepository,
                          Issue             $issue)
    {
        $data = $request->validated();
        $this->authorize('create', [$issue, $data['project_id']]);

        $issue = $issueRepository->createIssue($data);
        return response([
            'message' => 'Issue created successfully',
            'code' => 201,
            'issue' => $issue
        ], 201);
    }

    public function show(ShowIssueRequest $request,
                         IssueRepository  $issueRepository)
    {
        $data = $request->validated();
        $projectId = $data['project_id'];
        $issueId = $data['issue_id'];
        return $issueRepository->showIssue($projectId, $issueId);
    }

    public function update(UpdateIssueRequest     $request,
                           IssueRepository        $issueRepository,
                           NotificationRepository $notificationRepository,
                           UserRepository         $userRepository,
                           Issue                  $issue)
    {
        $data = $request->validated();
        $this->authorize('update', [$issue, $data['project_id']]);
        $type = 'issue';
        $action = 'Update';
        $userWhoDid = $userRepository->getUserById($data['user_id']);
        $issue = $issueRepository->getIssueById($data);
        $issueId = $data['issue_id'];
        $projectIdentify = $data['project_identify'];
        $url = "https://www.taskat.approved.tech/projects/$projectIdentify/backlog/?issue_details=$issueId";
        DB::beginTransaction();
        try {
            if (isset($data['title'])) {
                $issueRepository->updateIssueTitle($data);
                $title = 'update an issue';
                $content = $userWhoDid->name . ' updated an issue title';
                $notificationRepository
                    ->hadlePushNotofictionForIssue($data, $type, $title, $issueId, $action, $content);
            }
            if (isset($data['type'])) {
                $issueRepository->updateIssueType($data);
            }
            if (array_key_exists('sprint_id', $data)) {
                $issueRepository->updateIssueSprint($data);
            }
            if (isset($data['status_id'])) {
                $issueRepository->updateIssueStatus($data, $issueId);
                $title = 'update an issue';
                $content = $userWhoDid->name . ' updated an issue status';
                $notificationRepository
                    ->hadlePushNotofictionForStatusIssue($data, $type, $title, $issueId, $action, $content);
            }
            if (array_key_exists('description', $data)) {
                $title = 'update an issue';
                $content = $userWhoDid->name . ' updated an issue description';
                $notificationRepository
                    ->hadlePushNotofictionForIssue($data, $type, $title, $issueId, $action, $content);

                if (isset($data['mentionList'])) {
                    $mentions = $issueRepository->mention($issue->mention_list, $data['mentionList']);
                    if (!empty($mentions)) {
                        $content = $userWhoDid->name . ' mentioned you on as issue';
                        $notification = $notificationRepository
                            ->storeIssueNotification($data, $type,
                                'mention of you', $issueId, 'mention', $content);
                        foreach ($mentions as $mention) {
                            $user = $userRepository->getUserById($mention);
                            if ($user->id == $data['user_id'] && $user->player != null) {
                                $notificationRepository->storeRecipient($notification->id, $user->id);
                                PushNotificationJob::dispatch($user, $title, $content, $url);
                            }
                        }
                    }
                }
                $issueRepository->updateIssueDescription($data);
            }
            if (array_key_exists('assign_to', $data)) {
                $title = 'assigned an issue';
                $content = $userWhoDid->name . ' assigned you to an issue';
                $issueRepository->updateIssueAssign($data, $issueId);
                $notificationRepository
                    ->hadlePushNotofictionForIssue($data, $type, $title, $issueId, $action, $content);
            }
            if (array_key_exists('estimated_at', $data)) {
                $issueRepository->updateIssueEstimatedAt($data);
            }
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

    public function destroy(DeleteIssueRequest $request,
                            IssueRepository    $issueRepository,
                            Issue              $issue)
    {
        $data = $request->validated();
        $this->authorize('delete', [$issue, $data['project_id']]);

        $issueRepository->deleteTheIssue($data, $data['issue_id']);
        return response([
            'message' => 'Issue has been deleted.',
            'code' => 200,
        ], 200);
    }
}
