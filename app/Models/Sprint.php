<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sprint extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable =
        [
            'name',
            'goal',
            'start_date',
            'end_date',
            'duration',
            'status',
            'is_open',
            'is_completed',
            'deleted_at',
            'project_id',
            'user_id'
        ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function issues()
    {
        return $this->hasMany(Issue::class)
            ->orderBy('order');
    }

    // ******* Scope *******

    ## Global Scope ##

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('deleted', function (Builder $builder) {
            $builder->whereNull('deleted_at');
        });

        static::addGlobalScope('is_completed', function (Builder $builder) {
            $builder->where('is_completed', '=', 0);
        });
    }
}
