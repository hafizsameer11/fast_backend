<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParcelPayment extends Model
{
    use HasFactory;
    protected $fillable = [
        'parcel_id',
        'amount',
        'payment_method',
        'payment_status',
        'payment_reference',
        'delivery_fee',
        'is_pod',
        'paying_user',
        'delivery_fee_status',
        'total_amount',
    ];
    public function parcel()
    {
        return $this->belongsTo(SendParcel::class, 'parcel_id');
    }
}
