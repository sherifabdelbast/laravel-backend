<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Label extends Model
{
    use HasFactory,SoftDeletes ;

    protected $fillable = [
        'name',
        'project_id'
    ];

    public function project(){
        return $this->belongsTo(Project::class);
    }

    public function projectHistories()
    {
        return $this->hasMany(ProjectHistory::class);
    }

    public function issueLabels()
    {
        return $this->hasMany(IssueLabel::class);
    }
}
