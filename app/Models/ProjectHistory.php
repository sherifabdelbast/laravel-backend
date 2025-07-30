<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'status',
        'type',
        'action',
        'issue_id',
        'new_data',
        'old_data',
        'user_received_action',
        'sprint_received_action',
        'status_received_action',
        'label_received_action',
        'role_id',
        'project_id',
        'user_id',
    ];

    public function issue()
    {
        return $this->belongsTo(Issue::class)
            ->select(['id', 'title', 'status_id', 'project_id', 'assign_to'])
            ->withoutGlobalScopes();
    }

    public function user()
    {
        return $this->belongsTo(User::class)
            ->select(['id', 'name', 'email']);
    }

    public function userWhoReceivedAction()
    {
        return $this->belongsTo(User::class, 'user_received_action')
            ->select(['id', 'name', 'email']);
    }

    public function sprintWhichReceivedAction()
    {
        return $this->belongsTo(Sprint::class, 'sprint_received_action')
            ->select(['id', 'name'])
            ->withoutGlobalScopes();
    }

    public function project()
    {
        return $this->belongsTo(Project::class)
            ->select(['id', 'name']);
    }

    public function statusWhichReceivedAction()
    {
        return $this->belongsTo(Status::class, 'status_received_action')
            ->select([
                'id',
                'name',
                'type',
                'user_id',
            ])->withoutGlobalScopes();
    }

    public function labelWhichReceivedAction()
    {
        return $this->belongsTo(Label::class, 'label_received_action')
            ->withoutGlobalScopes();
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id')
            ->select(['id','name','key'])
            ->withoutGlobalScopes();
    }
}
