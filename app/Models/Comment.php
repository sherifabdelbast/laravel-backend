<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'content',
        'referred_to',
        'mention_list',
        'deleted_at',
        'issue_id',
        'project_id',
        'user_id',
        'sub_id',
    ];
    protected $casts = [
        'mention_list' => 'array'

    ];

    public function user()
    {
        return $this->belongsTo(User::class)
            ->select([
                'id',
                'name',
                'email',
                'photo',
            ]);
    }

    public function referredTo()
    {
        return $this->belongsTo(User::class, 'referred_to')
            ->select([
                'id',
                'name',
                'email',
                'photo',
            ]);
    }

    public function project_id()
    {
        return $this->belongsTo(Project::class)
            ->select([
                'id',
                'name',
                'icon'
            ]);
    }

    public function issue_id()
    {
        return $this->belongsTo(Issue::class)
            ->select([
                'title',
                'status_id',
                'description',
                'assign_to',
                'estimated_at',
            ]);
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
