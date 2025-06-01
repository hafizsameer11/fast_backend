<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tier extends Model
{
    use HasFactory;
    protected $fillable = [
        'tier',
        'no_of_rides',
        'commission',
        'tier_amount',
        'status',
    ];
}
