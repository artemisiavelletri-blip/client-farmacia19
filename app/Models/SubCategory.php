<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use App\Models\Product;

class SubCategory extends Model
{
    protected $table = 'subcategory';

    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'token'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function products_number()
    {
        return Product::where('subcategory_id',$this->id)->count();
    }
}
