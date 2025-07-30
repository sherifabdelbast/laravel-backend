<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestForgetPassword extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'identify_number',
        'previously_used',
        'expired_at'
    ];
}
