<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Issue extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'status_id',
        'type',
        'key',
        'priority',
        'description',
        'assign_to',
        'estimated_at',
        'mention_list',
        'order',
        'order_by_status',
        'is_completed',
        'deleted_at',
        'parent_id',
        'sprint_id',
        'project_id',
        'user_id',
    ];

    protected $casts = [
        'estimated_at' => 'array',
        'mention_list' => 'array'
    ];

    protected $appends = ['project_identify'];

    public function ProjectIdentify(): Attribute
    {
        $project = Project::query()
            ->projectId($this->project_id)
            ->first();
        return Attribute::make(get: fn() => $project->project_identify);
    }

    public function status()
    {
        return $this->belongsTo(Status::class)
            ->select([
                'id',
                'name',
                'type'
            ]);
    }

    public function user()
    {
        return $this->belongsTo(User::class)
            ->select([
                'id',
                'identify_number',
                'name',
                'email',
                'photo',
            ]);
    }

    public function teamMember()
    {
        return $this->belongsTo(Team::class, 'assign_to')
            ->select(['id', 'user_id', 'project_id']);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function issueHistories()
    {
        return $this->hasMany(IssueHistory::class);
    }

    public function projectHistories()
    {
        return $this->hasMany(ProjectHistory::class);
    }

    public function issueFiles()
    {
        return $this->hasMany(IssueFiles::class)
            ->orderBy('created_at', 'DESC');
    }

    public function comments()
    {

        return $this->hasMany(Comment::class);
    }

    public function sprint()
    {
        return $this->belongsTo(Sprint::class)
            ->select(['id', 'name']);
    }

    public function subIssues()
    {
        return $this->hasMany(Issue::class, 'parent_id');
    }

    public function issueLabels()
    {
        return $this->hasMany(IssueLabel::class);
    }

    // ******* Scope *******

    ## Global Scope ##

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope('deleted_at', function (Builder $builder) {
            $builder->whereNull('deleted_at');
        });

        static::addGlobalScope('is_completed', function (Builder $builder) {
            $builder->where('is_completed', '=', 0);
        });
    }

    ## Local Scope ##

    public function ScopeId(Builder $builder, $issueId)
    {
        $builder->where('id', '=', $issueId);
    }


    public function ScopeProject(Builder $builder, $projectId)
    {
        $builder->where('project_id', '=', $projectId);
    }

    public function ScopeParent(Builder $builder)
    {
        $builder->whereNull('parent_id');
    }

    public function scopeLabelName(Builder $builder)
    {
        $builder->with('Issuelabels.label')
            ->pluck('Issuelabels')
            ->toArray();
    }
}
