<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SendParcel extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id', 'total_amount', 'sender_address', 'receiver_address',
        'sender_name', 'sender_phone', 'receiver_name', 'receiver_phone',
        'parcel_category', 'parcel_value', 'description', 'payer', 
        'amount', 'delivery_fee'
    ];
}
