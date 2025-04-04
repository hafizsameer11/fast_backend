<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiderVerification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'first_name', 'last_name', 'email_address', 'phone', 'address', 'nin_number',
        'vehicle_type', 'plate_number', 'riders_permit_number', 'color',
        'passport_photo', 'rider_permit_upload', 'vehicle_video'
    ];
}
