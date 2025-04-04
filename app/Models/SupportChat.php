<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupportChat extends Model
{
    use HasFactory;

    protected $fillable = [
        'sender_id',
        'receiver_id',
        'sender_type',
        'receiver_type',
        'message',
        'status'
    ];

    // You can define relationships if needed (e.g. to User, Rider, Admin models)
}
