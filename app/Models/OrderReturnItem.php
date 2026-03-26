<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderReturnItem extends Model
{
    protected $fillable = ['return_id','product_id'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function return()
    {
        return $this->belongsTo(OrderReturn::class, 'return_id');
    }
}