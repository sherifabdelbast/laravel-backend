<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Traits\HasRoles;

class Team extends Model
{
    use HasFactory, SoftDeletes, HasRoles;

    protected $fillable = [
        'access',
        'invite_status',
        'deleted_at',
        'role_id',
        'project_id',
        'user_id',
    ];

    protected array $guard_name = ['customer'];

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

    public function invitation()
    {
        return $this->belongsTo(Project::class);
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function issues()
    {
        return $this->hasMany(Issue::class);
    }


    public function assignFrom()
    {
        return $this->belongsTo(IssueHistory::class);
    }

    public function assignTo()
    {
        return $this->belongsTo(IssueHistory::class);
    }


    // ******* Scope *******

    ## Global Scope ##
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('deleted_at', function (Builder $builder) {
            $builder->whereNull('deleted_at');
        });

        static::addGlobalScope('access', function (Builder $builder) {
            $builder->where('access', '=', 1)
                ->orWhere('access', '=', null);
        });
    }

    public function ScopeAccept(Builder $builder)
    {
        $builder->where('access', '=', 1);
    }
}
