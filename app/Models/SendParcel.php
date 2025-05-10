<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SendParcel extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'user_id',
        'total_amount',
        'sender_address',
        'receiver_address',
        'sender_name',
        'sender_phone',
        'receiver_name',
        'receiver_phone',
        'parcel_category',
        'parcel_value',
        'description',
        'payer',
        'amount',
        'delivery_fee',
        'status',
        'ordered_at',
        'picked_up_at',
        'in_transit_at',
        'delivered_at',
        'is_pickup_confirmed',      // ✅ ensure this is here
        'is_delivery_confirmed',    // ✅ ensure this is here
        'current_latitude',
        'current_longitude',

        'parcel_name',
        'payment_method',
        'pay_on_delivery',
        'schedule_type',
        'scheduled_date',
        'scheduled_time',

    ];

    protected $casts = [
        'ordered_at' => 'datetime',
        'picked_up_at' => 'datetime',
        'in_transit_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    public function bids()
    {
        return $this->hasMany(ParcelBid::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function acceptedBid()
    {
        return $this->belongsTo(ParcelBid::class, 'accepted_bid_id');
    }
}
