<?php

namespace App\Repositories;

use App\Models\Comment;
use App\Models\Issue;
use App\Models\Notification;
use App\Models\Recipient;

class CommentRepository
{
    public function getCommentById($commentId)
    {
        return Comment::query()
            ->where('id', '=', $commentId)
            ->first();
    }

    public function createComments($data)
    {
        return Comment::query()
            ->create([
                'content' => $data['content'],
                'issue_id' => $data['issue_id'],
                'project_id' => $data['project_id'],
                'user_id' => $data['user_id'],
            ]);

    }

    public function listOfComments($projectId, $issueId)
    {
        return Comment::query()
            ->where('project_id', '=', $projectId)
            ->where('issue_id', '=', $issueId)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();
    }


    public function deleteComment($commentId)
    {
        return Comment::query()
            ->where('id', '=', $commentId)
            ->delete();
    }

    public function editComment($commentId, $data)
    {
        return Comment::query()
            ->where('id', '=', $commentId)
            ->update([
                'content' => $data['content'],
            ]);
    }
}
