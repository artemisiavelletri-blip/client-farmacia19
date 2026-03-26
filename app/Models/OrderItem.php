<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'product_name',
        'price',
        'vat_percentage',
        'quantity',
        'subtotal',
        'vat_amount',
    ];

    /**
     * Relazione con l'ordine
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Relazione con il prodotto originale
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}