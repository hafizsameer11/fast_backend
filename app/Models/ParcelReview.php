<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParcelReview extends Model
{
    use HasFactory;
    protected $fillable = [
        'send_parcel_id',
        'to_user_id',
        'from_user_id',
        'rating',
        'review',
    ];

    public function fromUser()
    {
        return $this->belongsTo(\App\Models\User::class, 'from_user_id');
    }

    public function toUser()
    {
        return $this->belongsTo(\App\Models\User::class, 'to_user_id');
    }
}
