<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Affiliate extends Model
{
    //
    protected $fillable = [
        'user_id',
        'affiliate_code',
        'registrations',
        'balance',
    ];

        protected static function boot()
    {
        parent::boot();

        static::creating(function ($affiliate) {
            if (empty($affiliate->affiliate_code)) {
                $affiliate->affiliate_code = self::generateAffiliateCode(7);
            }
        });
    }

    // ✅ دالة توليد كود قصير (6 أو 7 حروف)
    public static function generateAffiliateCode($length = 6)
    {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        return substr(str_shuffle($characters), 0, $length);
    }


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function earnings()
    {
        return $this->hasMany(AffiliateEarning::class);
    }
}
