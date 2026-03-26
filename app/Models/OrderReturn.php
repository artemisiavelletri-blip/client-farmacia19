<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderReturn extends Model
{
    use HasFactory;

    protected $table = 'order_returns';
    protected $fillable = [
        'order_id',
        'reason_id',
        'message',
        'image1',
        'image2',
        'image3'
    ];

    // Relazioni
    public function reason()
    {
        return $this->belongsTo(\App\Models\ReasonReturn::class, 'reason_id');
    }

    public function order()
    {
        return $this->belongsTo(\App\Models\Order::class, 'order_id');
    }

    public function items()
    {
        return $this->hasMany(OrderReturnItem::class, 'return_id');
    }
}
