<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class IssueLabel extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'issue_id',
        'label_id'
    ];

    public function issue(){
        return $this->belongsTo(Issue::class,'issue_id');
    }

    public function label(){
        return $this->belongsTo(Label::class,'label_id');
    }

}
