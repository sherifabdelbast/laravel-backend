<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestHistory extends Model
{
    use HasFactory;
    protected $fillable = [
        'identify_number',
        'token',
        'expired_at',
    ];
}
