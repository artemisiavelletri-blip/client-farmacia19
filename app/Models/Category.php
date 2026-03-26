<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use App\Models\Brand;
use App\Models\Product;

class Category extends Model
{
    protected $table = 'category';

    use HasFactory;

    protected $fillable = [
        'name',
        'token'
    ];

    public function subCategories()
    {
        return $this->hasMany(SubCategory::class);
    }

    public function brands()
    {
        return $this->hasManyThrough(
            Brand::class,    // Modello finale
            Product::class,  // Modello intermedio
            'category_id',   // FK su Product verso Category
            'id',            // PK di Brand
            'id',            // PK di Category
            'brand_id'       // FK su Product verso Brand
        )->distinct()->get();      // evita duplicati
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function productsBestseller()
    {
        return $this->hasMany(Product::class)->where('bestseller', 1)->limit(4);
    }
}
