<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Promotion extends Model
{
    protected $table = 'discount';

    use HasFactory;

    protected $fillable = [
        'token',
        'product_id',
        'category_id',
        'subcategory_id',
        'percentage',
        'active'
    ];

    // Promotion.php
    public function cartItems()
    {
        return $this->belongsToMany(
            CartItem::class,
            'cart_item_discount',
            'discount_id',
            'cart_item_id'
        );
    }

    public function isValid(): bool
    {
        if (! $this->active) {
            return false;
        }

        $today = now()->toDateString();

        if ($this->start_date && $this->start_date > $today) {
            return false;
        }

        if ($this->end_date && $this->end_date < $today) {
            return false;
        }

        return true;
    }

    public function appliesToProduct(Product $product): bool
    {
        return $this->all_products
            || ($this->product_id && $this->product_id == $product->id)
            || ($this->brand_id && $this->brand_id == $product->brand_id)
            || ($this->category_id && $this->category_id == $product->category_id);
    }
}
