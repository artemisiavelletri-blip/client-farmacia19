<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    protected $fillable = ['user_id', 'product_id', 'quantity'];

    public function product() {
        return $this->belongsTo(Product::class);
    }

    public function getSubtotalAttribute()
    {
        if (!$this->product) {
            return 0;
        }

        return $this->product->final_price * $this->quantity;
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
}

