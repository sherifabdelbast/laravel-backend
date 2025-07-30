<?php

namespace App\Http\Controllers\Comment;

use App\Http\Controllers\Controller;
use App\Http\Requests\Comment\CreateCommentRequest;
use App\Http\Requests\Comment\DeleteCommentRequest;
use App\Http\Requests\Comment\EditCommentRequest;
use App\Http\Requests\Comment\ListCommentRequest;
use App\Jobs\PushNotificationJob;
use App\Models\Comment;
use App\Repositories\CommentRepository;
use App\Repositories\IssueRepository;
use App\Repositories\NotificationRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\DB;

class CommentController extends Controller
{
    public function index(ListCommentRequest $request,
                          CommentRepository  $commentRepository)
    {
        $data = $request->validated();
        $projectId = $data['project_id'];
        $issueId = $data['issue_id'];
        $comments = $commentRepository->listOfComments($projectId, $issueId);
        return response([
            'message' => 'List of comments',
            'code' => 200,
            'comments' => $comments
        ], 200);
    }

    public function store(CreateCommentRequest   $request,
                          CommentRepository      $commentRepository,
                          NotificationRepository $notificationRepository,
                          UserRepository         $userRepository,
                          Comment                $comments)
    {
        $data = $request->validated();

        $this->authorize('create', [$comments, $data['project_id']]);
        $type = 'issue';
        $action = 'Create';
        $title = 'create comment';
        $issueId = $data['issue_id'];
        $projectIdentify = $data['project_identify'];
        $url = "https://www.taskat.approved.tech/projects/$projectIdentify/backlog/?issue_details=$issueId";
        DB::beginTransaction();
        try {
            $userWhoDid = $userRepository->getUserById($data['user_id']);
            $comment = $commentRepository->createComments($data);

            if (isset($data['mentionList'])) {
                $mentions = $data['mentionList'];
                if (!empty($mentions)) {
                    $contentMention = $userWhoDid->name . ' mentioned you in comment';
                    $notification = $notificationRepository
                        ->storeIssueNotification($data, $type,
                            'mentioned you', $issueId, 'mention', $contentMention);
                    foreach ($mentions as $mention) {
                        $user = $userRepository->getUserById($mention);
                        if ($user->id != $data['user_id'] && $user->player_ids != null) {
                            $notificationRepository->storeRecipient($notification->id, $user->id);
                            $title = 'mentioned you';
                            PushNotificationJob::dispatch($user, $title, $contentMention, $url);
                        }
                    }
                }
            }
            $contentComment = $userWhoDid->name . ' create comment in issue';
            $notificationRepository->hadlePushNotofictionForIssue($data, $type,
                $title, $issueId, $action, $contentComment);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
        return response([
            'message' => 'comment created successfully',
            'code' => 201,
            'comment' => $comment
        ], 201);
    }

    public function update(EditCommentRequest     $request,
                           CommentRepository      $commentRepository,
                           NotificationRepository $notificationRepository,
                           IssueRepository        $issueRepository,
                           UserRepository         $userRepository,
                           Comment                $comments)
    {
        $data = $request->validated();
        $this->authorize('update', [$comments, $data['project_id']]);
        $commentId = $data['comment_id'];
        $type = 'issue';
        $issueId = $data['issue_id'];
        $projectIdentify = $data['project_identify'];
        $url = "https://www.taskat.approved.tech/projects/$projectIdentify/backlog/?issue_details=$issueId";
        DB::beginTransaction();
        try {
            $comment = $commentRepository->getCommentById($commentId);
            $userWhoDid = $userRepository->getUserById($data['user_id']);
            if (isset($data['mentionList'])) {
                $mentions = $issueRepository->mention($comment->mention_list, $data['mentionList']);
                if (!empty($mentions)) {
                    $contentMention = $userWhoDid->name . ' mentioned you in comment';
                    $notification = $notificationRepository
                        ->storeIssueNotification($data, $type,
                            'mentioned you', $issueId, 'mention', $contentMention);
                    foreach ($mentions as $mention) {
                        $user = $userRepository->getUserById($mention);
                        if ($user->id != $data['user_id'] && $user->player_ids != null) {
                            $notificationRepository->storeRecipient($notification->id, $user->id);
                            $title = 'mentioned you';
                            PushNotificationJob::dispatch($user, $title, $contentMention, $url);
                        }
                    }
                }
            }
            $commentRepository->editComment($commentId, $data);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
        return response([
            'message' => 'comment updated successfully',
            'code' => 200,
        ], 200);
    }

    public function destroy(DeleteCommentRequest $request,
                            CommentRepository    $commentRepository,
                            Comment              $comments)
    {
        $data = $request->validated();
        $this->authorize('delete', [$comments, $data['project_id']]);

        $commentId = $data['comment_id'];
        $commentRepository->deleteComment($commentId);
        return response([
            'message' => 'comment deleted',
            'code' => 200,
        ], 200);

    }
}
