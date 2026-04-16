<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{

    protected $table = 'products';

    use HasFactory;

    protected $fillable = [
        'ean',
        'minsan',
        'name',
        'price',
        'discountedPrice',
        'brand_id',
        'category',
        'stock',
        'description'
    ];

    public function brand()
    {
        return $this->belongsTo(Brand::class)->first();
    }

    public function brandRelation()
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }

    public function getFinalPriceAttribute()
    {
        return $this->discountPrice && $this->discountPrice > 0
        ? $this->discountPrice
        : $this->price;
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    public function relatedProducts($limit = 4)
    {
        // Prende i tag del prodotto
        $tagIds = $this->tags()->pluck('tags.id');

        // Trova altri prodotti con gli stessi tag, escludendo il prodotto corrente
        return Product::whereHas('tags', function($q) use ($tagIds) {
                $q->whereIn('tags.id', $tagIds);
            })
            ->where('id', '<>', $this->id)
            ->distinct()
            ->take($limit)
            ->get();
    }

    public function iva()
    {
        return $this->belongsTo(Iva::class);
    }

    protected static function booted()
    {
        static::addGlobalScope('not_hidden', function (Builder $builder) {
            $builder->where('hidden', 0);
        });
    }

}

