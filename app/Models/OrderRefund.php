<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderRefund extends Model
{
    protected $fillable = [
        'order_id',
        'order_return_id',
        'stripe_refund_id',
        'amount',
        'status',
        'reason'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function return()
    {
        return $this->belongsTo(OrderReturn::class, 'order_return_id');
    }
}