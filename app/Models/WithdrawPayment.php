<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WithdrawPayment extends Model
{
    //
    protected $fillable = [
        'user_id',
        'amount',
        'status',
        'phone',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
