<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'name_en',
        'price',
        'item_id',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
