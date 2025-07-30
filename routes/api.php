<?php

use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\UserController;
use App\Http\Controllers\Comment\CommentController;
use App\Http\Controllers\History\IssueHistoryController;
use App\Http\Controllers\History\ProjectHistoryController;
use App\Http\Controllers\Issue\BacklogController;
use App\Http\Controllers\Issue\BoardController;
use App\Http\Controllers\Issue\BulkOfIssuesController;
use App\Http\Controllers\Issue\CopyIssueController;
use App\Http\Controllers\Issue\IssueController;
use App\Http\Controllers\Issue\LabelController;
use App\Http\Controllers\Issue\MoveBulkOfIssuesController;
use App\Http\Controllers\Issue\TypeIssueController;
use App\Http\Controllers\Issue\UpdatePriorityIssueController;
use App\Http\Controllers\Issue\UploadFilesIssueController;
use App\Http\Controllers\Notification\NotificationController;
use App\Http\Controllers\Notification\UserPlayerIdsController;
use App\Http\Controllers\Profile\ChangePasswordController;
use App\Http\Controllers\Profile\ProfileController;
use App\Http\Controllers\Project\ArchiveProjectController;
use App\Http\Controllers\Project\FavoriteProjectsController;
use App\Http\Controllers\Project\KeyProjectsController;
use App\Http\Controllers\Project\ProjectController;
use App\Http\Controllers\Role\PermissionController;
use App\Http\Controllers\Role\RoleController;
use App\Http\Controllers\Sprint\CreateSprintWithBulkIssues;
use App\Http\Controllers\Sprint\OpenSprintController;
use App\Http\Controllers\Sprint\SprintController;
use App\Http\Controllers\Sprint\StartSprintController;
use App\Http\Controllers\Status\MoveStatusController;
use App\Http\Controllers\Status\StatusController;
use App\Http\Controllers\Team\AllTeamMembersOfAllProjects;
use App\Http\Controllers\Team\CheckInvitationRequestController;
use App\Http\Controllers\Team\InvitationController;
use App\Http\Controllers\Team\TeamController;
use App\Http\Controllers\Team\TeamMembersAcceptedController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::group(['middleware' => ['auth:sanctum']], function () {
    //auth
    Route::post('/logout', [LogoutController::class, 'destroy']);
    Route::get('/user', [UserController::class, 'show']);
    Route::get('/profile/{userIdentify}', [ProfileController::class, 'show']);
    Route::post('/profile/{userIdentify}/edit', [ProfileController::class, 'update']);
    Route::post('/profile/{userIdentify}/change-password', [ChangePasswordController::class, 'update']);

    //project
    Route::get('/projects', [ProjectController::class, 'index']);
    Route::post('/projects/create', [ProjectController::class, 'store']);
    Route::get('/projects/{projectIdentify}/details', [ProjectController::class, 'show']);
    Route::post('/projects/{projectIdentify}/edit', [ProjectController::class, 'update']);
    Route::get('/projects/{projectIdentify}/favorite', FavoriteProjectsController::class);
    Route::get('/projects/{projectIdentify}/archive', [ArchiveProjectController::class, 'destroy']);
    Route::get('/projects/{projectIdentify}/history', [ProjectHistoryController::class, 'index']);
    Route::get('/projects/key-projects', [KeyProjectsController::class, 'index']);

    //team
    Route::get('/projects/{projectIdentify}/team', [TeamController::class, 'index']);
    Route::post('/projects/{projectIdentify}/team/invite', [InvitationController::class, 'store']);
    Route::post('/invitation/accept', [InvitationController::class, 'update']);
    Route::post('/projects/{projectIdentify}/team/{teamMemberId}/delete', [TeamController::class, 'destroy']);
    Route::post('/projects/{projectIdentify}/team/{teamMemberId}/edit-role', [TeamController::class, 'update']);
    Route::get('/projects/{projectIdentify}/accepted-team', [TeamMembersAcceptedController::class, 'index']);
    Route::get('/your-work-teamMembers', [AllTeamMembersOfAllProjects::class, 'index']);

    //status
    Route::get('/all-statuses', [StatusController::class, 'index']);
    Route::get('/projects/{projectIdentify}/statuses', [StatusController::class, 'show']);
    Route::post('/projects/{projectIdentify}/statuses/create', [StatusController::class, 'store']);
    Route::post('/projects/{projectIdentify}/statuses/{statusId}/edit', [StatusController::class, 'update']);
    Route::post('/projects/{projectIdentify}/statuses/{statusId}/move', [MoveStatusController::class, 'update']);
    Route::post('/projects/{projectIdentify}/statuses/{statusId}/delete', [StatusController::class, 'destroy']);

    //issue
    Route::get('/issues', [IssueController::class, 'index']);
    Route::post('/projects/{projectIdentify}/issues/create', [IssueController::class, 'store']);
    Route::get('/projects/{projectIdentify}/backlog/issues/{issueId}/show', [IssueController::class, 'show']);
    Route::post('/projects/{projectIdentify}/backlog/issues/{issueId}/edit', [IssueController::class, 'update']);
    Route::post('/projects/{projectIdentify}/backlog/issues/{issueId}/edit-priority',
        [UpdatePriorityIssueController::class, 'update']);
    Route::get('/projects/{projectIdentify}/backlog', [BacklogController::class, 'index']);
    Route::post('/projects/{projectIdentify}/backlog/issues/{issueId}/delete', [IssueController::class, 'destroy']);
    Route::post('/projects/{projectIdentify}/backlog/bulk-issues/delete', [BulkOfIssuesController::class, 'destroy']);
    Route::post('/projects/{projectIdentify}/backlog/bulk-issues/edit', [BulkOfIssuesController::class, 'update']);
    Route::post('/projects/{projectIdentify}/backlog/bulk-issues/move', [MoveBulkOfIssuesController::class, 'update']);
    Route::post('/projects/{projectIdentify}/backlog/issues/{issueId}/move', [BacklogController::class, 'update']);
    Route::get('/projects/{projectIdentify}/backlog/issues/{issueId}/history', [IssueHistoryController::class, 'index']);
    Route::get('/projects/{projectIdentify}/issues/{issueId}/type-issue', [TypeIssueController::class, 'index']);
    Route::post('/projects/{projectIdentify}/backlog/issues/{issueId}/upload', [UploadFilesIssueController::class, 'store']);
    Route::post('/projects/{projectIdentify}/backlog/issues/{issueId}/file/{fileId}/delete',
        [UploadFilesIssueController::class, 'destroy']);
    Route::post('/projects/{projectIdentify}/backlog/issues/{issueId}/copy', [CopyIssueController::class, 'store']);

    //sprint
    Route::get('/projects/{projectIdentify}/backlog/sprints', [SprintController::class, 'index']);
    Route::get('/projects/{projectIdentify}/sprints-open', [OpenSprintController::class, 'index']);
    Route::post('/projects/{projectIdentify}/backlog/sprints/{sprintId}/edit', [SprintController::class, 'update']);
    Route::post('/projects/{projectIdentify}/backlog/sprints/create', [SprintController::class, 'store']);
    Route::post('/projects/{projectIdentify}/backlog/sprints/create-issues', [CreateSprintWithBulkIssues::class, 'store']);
    Route::post('/projects/{projectIdentify}/backlog/sprints/{sprintId}/delete', [SprintController::class, 'destroy']);
    Route::post('/projects/{projectIdentify}/backlog/sprints/{sprintId}/start', [StartSprintController::class, 'update']);
    Route::post('/projects/{projectIdentify}/backlog/sprints/{sprintId}/complete', [StartSprintController::class, 'destroy']);

    //board
    Route::get('/projects/{projectIdentify}/board', [BoardController::class, 'index']);
    Route::post('/projects/{projectIdentify}/board/issues/{issueId}/move', [BoardController::class, 'update']);

    //comment
    Route::post('/projects/{projectIdentify}/backlog/issues/{issueId}/comments/create', [CommentController::class, 'store']);
    Route::get('/projects/{projectIdentify}/backlog/issues/{issueId}/comments', [CommentController::class, 'index']);
    Route::post('/projects/{projectIdentify}/backlog/issues/{issueId}/comments/{commentId}/delete',
        [CommentController::class, 'destroy']);
    Route::post('/projects/{projectIdentify}/backlog/issues/{issueId}/comments/{commentId}/edit',
        [CommentController::class, 'update']);

    //Role
    Route::get('/projects/{projectIdentify}/role', [RoleController::class, 'index']);
    Route::post('/projects/{projectIdentify}/role/create', [RoleController::class, 'store']);
    Route::post('/projects/{projectIdentify}/role/{roleId}/edit', [RoleController::class, 'update']);
    Route::post('/projects/{projectIdentify}/role/{roleId}/delete', [RoleController::class, 'destroy']);
    Route::get('/projects/{projectIdentify}/all-permissions', [PermissionController::class, 'index']);
    Route::get('/projects/{projectIdentify}/member-role-permissions', [PermissionController::class, 'show']);

    //label
    Route::get('/projects/{projectIdentify}/labels',[LabelController::class, 'index']);
    Route::post('/projects/{projectIdentify}/issue/{issueId}/label/create',[LabelController::class, 'store']);
    Route::post('/projects/{projectIdentify}/issue/{issueId}/label/delete',[LabelController::class, 'destroy']);

    //notification
    Route::post('/player-id', [UserPlayerIdsController::class, 'store']);
    Route::post('/notification', [NotificationController::class, 'index']);
    Route::post('/notification/{notificationId}/read', [NotificationController::class, 'update']);
    Route::post('/notification/{notifyId}/delete', [NotificationController::class, 'destroy']);

});
Route::post('/invitation', CheckInvitationRequestController::class);
