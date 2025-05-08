<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParcelBid extends Model
{
    protected $fillable = [
        'send_parcel_id',
        'rider_id',
        'user_id',
        'bid_amount',
        'message',
        'status',
        'created_by',
    ];
    

    public function parcel()
    {
        return $this->belongsTo(SendParcel::class, 'send_parcel_id');
    }

    public function rider()
    {
        return $this->belongsTo(User::class, 'rider_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}


