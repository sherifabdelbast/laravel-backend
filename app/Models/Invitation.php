<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invitation extends Model
{
    use HasFactory;

    protected $fillable =
        [
            'message',
            'invite_identify',
            'member_id',
            'role_id',
            'project_id',
            'user_id',
        ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function teamMember()
    {
        return $this->belongsTo(Team::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }
}
