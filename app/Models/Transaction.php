<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $guarded = ['id'];
    protected $casts = [
        'raw_response' => 'array'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
