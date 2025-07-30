<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IssueHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'status',
        'type',
        'old_data',
        'new_data',
        'new_estimated_at',
        'old_estimated_at',
        'issue_id',
        'assign_from',
        'assign_to',
        'user_id',
    ];

    protected $casts = [
        'old_estimated_at' => 'array',
        'new_estimated_at' => 'array'
    ];

    public function issue()
    {
        return $this->belongsTo(Issue::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function assignFromTeamMember()
    {
        return $this->belongsTo(Team::class, 'assign_from');
    }

    public function assignToTeamMember()
    {
        return $this->belongsTo(Team::class, 'assign_to');
    }
}
