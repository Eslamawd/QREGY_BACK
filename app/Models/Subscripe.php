<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Subscripe extends Model
{
    //
        protected $fillable = [
        'user_id',
        'plan',
        'plan_id',
        'price',
        'start_date',
        'end_date',
    ];

      public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function isExpired()
{
    return $this->end_date < now();
}
      public function planSub()
    {
        return $this->belongsTo(PlanSubscripe::class, 'plan_id');
    }

     
}
