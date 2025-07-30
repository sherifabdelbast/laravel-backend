<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_identify',
        'name',
        'key',
        'description',
        'icon',
        'user_id'
    ];

    protected $appends = ['url_icon'];

    protected function urlIcon(): Attribute
    {
        if ($this->icon != null) {
            return Attribute::make(get: fn() => asset('storage/upload/projects_icon/' . $this->icon));
        }
        return Attribute::make(get: fn() => null);
    }


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function teamMembers()
    {
        return $this->hasMany(Team::class)
            ->withoutGlobalScope('access');
    }

    public function invitations()
    {
        return $this->hasMany(Invitation::class);
    }

    public function roles()
    {
        return $this->hasMany(Role::class);
    }

    public function statuses()
    {
        return $this->hasMany(Status::class);
    }

    public function sprints()
    {
        return $this->hasMany(Sprint::class);
    }

    public function clipboards()
    {
        return $this->hasMany(Clipboard::class);
    }

    public function issues()
    {
        return $this->hasMany(Issue::class);
    }

    public function label(){
        return $this->hasMany(Label::class);
    }


    public function projectHistories()
    {
        return $this->hasMany(ProjectHistory::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
    public function issueFiles()
    {
        return $this->hasMany(IssueFiles::class);
    }


    // *********** Scope ***********

    public function scopeProjectId($query, $projectId)
    {
        return $query->where('id', '=', $projectId);
    }

    public function scopeProjectIdentify($query, $projectIdentify)
    {
        return $query->where('project_identify', '=', $projectIdentify);
    }

}
