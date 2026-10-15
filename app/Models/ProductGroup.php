<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductGroup extends Model
{
    protected $table = 'product_groups';

    public function products()
    {
        return $this->hasMany(Product::class, 'product_group_id');
    }
}