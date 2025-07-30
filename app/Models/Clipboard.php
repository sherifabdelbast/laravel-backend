<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Clipboard extends Model
{
    use HasFactory;

    protected $fillable =
        [
            'default',
            'favorite',
            'archive',
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


    // ******* Scope *******

    public function ScopeArchive(Builder $builder)
    {
        $builder->where('archive', '=', 1);
    }
}
