<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    protected $fillable = ['user_id', 'product_id', 'quantity'];

    public function product() {
        return $this->belongsTo(Product::class);
    }

    public function getSubtotalNoDiscountAttribute()
    {
        if (!$this->product) {
            return 0;
        }

        return $this->product->final_price * $this->quantity;
    }

    public function getSubtotalAttribute()
    {
        if (!$this->product) {
            return 0;
        }

        $final_price = $this->product->final_price;

        if ($this->discounts->isNotEmpty()) {
            $discount = $this->discounts->first();
            $canApplyDiscount = true;

            if ($discount && $discount->isValid()) {

                if ($discount->minimum_purchase !== null) {
                    $canApplyDiscount = $this->get()->sum->subtotalnodiscount >= $discount->minimum_purchase;
                }

                if ($canApplyDiscount) {
                    if($discount->all_products){
                        if($discount->percentage){
                            $final_price -= $final_price * ($discount->percentage / 100);
                        }
                    } else if($discount->product_id && $this->product->id == $discount->product_id){
                        if($discount->percentage){
                            $final_price -= $final_price * ($discount->percentage / 100);
                        }
                    } else if($discount->brand_id && $this->product->brand_id == $discount->brand_id){
                        if($discount->percentage){
                            $final_price -= $final_price * ($discount->percentage / 100);
                        }
                    }
                }
            }
        }

        return $final_price * $this->quantity;
    }

    public function getDiscountAttribute()
    {
        if (!$this->product) {
            return 0;
        }

        $price = $this->product->final_price;
        $discountAmount = 0;

        if ($this->discounts->isNotEmpty()) {
            $discount = $this->discounts->first();
            $canApplyDiscount = true;

            if ($discount && $discount->isValid()) {

                if ($discount->minimum_purchase !== null) {
                    $canApplyDiscount = $this->get()->sum->subtotalnodiscount >= $discount->minimum_purchase;
                }

                if ($canApplyDiscount) {

                    if ($discount->all_products) {

                        if ($discount->percentage) {
                            $discountAmount = $price * ($discount->percentage / 100);
                        }

                    } elseif ($discount->product_id && $this->product->id == $discount->product_id) {

                        if ($discount->percentage) {
                            $discountAmount = $price * ($discount->percentage / 100);
                        }

                    } elseif ($discount->brand_id && $this->product->brand_id == $discount->brand_id) {

                        if ($discount->percentage) {
                            $discountAmount = $price * ($discount->percentage / 100);
                        } 

                    }
                }
            }
        }

        return $discountAmount * $this->quantity;
    }

    public function getSubtotalNoIvaAttribute()
    {
        if (!$this->product || !$this->product->iva) {
        return 0;
        }

        $priceWithVat = $this->product->final_price;
        $vatPercentage = $this->product->iva->percentage;

        // Scorporo IVA
        $priceWithoutVat = $priceWithVat / (1 + ($vatPercentage / 100));

        return $priceWithoutVat * $this->quantity;
    }

    public function getTotalVatAttribute()
    {
        if (!$this->product || !$this->product->iva) {
        return 0;
        }

        $priceWithVat = $this->product->final_price;

        $vatPercentage = $this->product->iva->percentage;

        // Scorporo IVA
        $priceWithoutVat = $priceWithVat / (1 + ($vatPercentage / 100));
        $vatAmount = $priceWithVat - $priceWithoutVat;

        return $vatAmount * $this->quantity;
    }

    // CartItem.php
    public function discounts()
    {
        return $this->belongsToMany(
            Promotion::class,
            'cart_item_discount',
            'cart_item_id',
            'discount_id'
        );
    }
}

