<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Withdrawal extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'amount', 'bank_name', 'account_name', 'account_number', 'status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
