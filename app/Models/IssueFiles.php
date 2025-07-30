<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\SoftDeletes;



class IssueFiles extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'files',
        'type',
        'size',
        'name',
        'issue_id',
        'sub_id'
    ];

protected $appends= ['url_file'];
    protected function urlFile(): Attribute
    {
        if ($this->files != null) {
            return Attribute::make(get: fn() => asset('storage/upload/issues_files/' . $this->files));
        }
        return Attribute::make(get: fn() => null);
    }

    public function issue(){
        return $this->belongsTo(Issue::class);
    }


    public function project(){
        return $this->belongsTo(Project::class);
    }
}
