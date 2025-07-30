<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'type',
        'action',
        'content',
        'invitation_id',
        'user_received_action',
        'issue_id',
        'role_id',
        'sprint_id',
        'project_id',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id')
            ->select([
                'id', 'identify_number', 'name', 'email','photo'
            ]);
    }

    public function project()
    {
        return $this->belongsTo(Project::class)
            ->select(['id', 'name', 'key']);
    }

    public function issue()
    {
        return $this->belongsTo(Issue::class, 'issue_id')
            ->select(['id', 'title', 'key', 'type', 'status_id', 'project_id']);
    }

    public function userReceivedAction()
    {
        return $this->belongsTo(User::class, 'user_received_action');
    }

    public function recipients()
    {
        return $this->hasMany(Recipient::class);
    }

    public function invitation()
    {
        return $this->belongsTo(Invitation::class);
    }

    public function sprint()
    {
        return $this->belongsTo(Sprint::class, 'sprint_id');
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

}
