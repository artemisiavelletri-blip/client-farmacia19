<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    protected $fillable = ['name'];

    public function products()
    {
        return Product::where('brand_id',$this->id);
    }
}

