<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlanSubscripe extends Model
{
    //
    protected $fillable = [
        'name',
        'name_en', 
        'max_restaurants', 
        'max_tables', 
        'max_items', 
        'vip_support',
        'price',
        'duration_days',
        'features',
    ];

    protected $casts = [
        'features' => 'array',
    ];
    
}
