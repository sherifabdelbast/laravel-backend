<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Recipient extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'notify_id',
        'read_at',
        'deleted_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id')
            ->select([
                'id', 'identify_number', 'name', 'email'
            ]);
    }

    public function notification()
    {
        return $this->belongsTo(Notification::class, 'notify_id');
    }
}
