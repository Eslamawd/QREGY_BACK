<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AffiliateEarning extends Model
{
    //
    protected $fillable = [
        'affiliate_id',
        'referred_user_id',
        'amount',
        'status',
    ];

    public function affiliate()
    {
        return $this->belongsTo(Affiliate::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'referred_user_id');
    }

    
}
