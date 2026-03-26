<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    protected $fillable = [
        'holder_name',
        'user_id',
        'stripe_payment_method_id',
        'brand',
        'last4',
        'exp_month',
        'exp_year',
        'is_default',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
