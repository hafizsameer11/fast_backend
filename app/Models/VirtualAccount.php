<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VirtualAccount extends Model
{
    use HasFactory;
    protected $fillable = [
        'account_number',
        'account_name',
        'user_id',
        'reference',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
