<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParcelBid extends Model
{
    protected $fillable = [
        'send_parcel_id', 'rider_id', 'bid_amount', 'message', 'status'
    ];

    public function parcel() {
        return $this->belongsTo(SendParcel::class, 'send_parcel_id');
    }

    public function rider() {
        return $this->belongsTo(User::class, 'rider_id');
    }
}
