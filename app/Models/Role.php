<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    use SoftDeletes;

    protected array $guard_name = ['customer'];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function projectHistories()
    {
        return $this->hasMany(ProjectHistory::class);
    }

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope('deleted_at', function (Builder $builder) {
            $builder->whereNull('deleted_at');
        });
    }
}
