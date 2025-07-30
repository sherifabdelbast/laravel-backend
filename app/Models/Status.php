<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Status extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'type',
        'max',
        'order',
        'deleted_at',
        'user_id',
        'project_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function issues()
    {
        return $this->hasMany(Issue::class)
            ->whereNotNull('sprint_id')
            ->orderBy('order_by_status');
    }

    // ******* Scope *******

    ## Global Scope ##
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('deleted_at', function (Builder $builder) {
            $builder->whereNull('deleted_at');
        });

    }
}
