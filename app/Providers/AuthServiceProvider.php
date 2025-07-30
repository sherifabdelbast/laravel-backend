<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use App\Models\Comment;
use App\Models\Issue;
use App\Models\IssueHistory;
use App\Models\Project;
use App\Models\ProjectHistory;
use App\Models\Role;
use App\Models\Sprint;
use App\Models\Status;
use App\Models\Team;
use App\Policies\CommentPolicy;
use App\Policies\IssueHistoryPolicy;
use App\Policies\IssuePolicy;
use App\Policies\ProjectHistoryPolicy;
use App\Policies\ProjectPolicy;
use App\Policies\RolePolicy;
use App\Policies\SprintPolicy;
use App\Policies\StatusPolicy;
use App\Policies\TeamPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Project::class => ProjectPolicy::class,
        Comment::class => CommentPolicy::class,
        Issue::class => IssuePolicy::class,
        IssueHistory::class => IssueHistoryPolicy::class,
        ProjectHistory::class => ProjectHistoryPolicy::class,
        Sprint::class => SprintPolicy::class,
        Role::class => RolePolicy::class,
        Status::class => StatusPolicy::class,
        Team::class => TeamPolicy::class,
    ];
}
