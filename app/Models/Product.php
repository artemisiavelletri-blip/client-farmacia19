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

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    protected static function booted()
    {
        static::addGlobalScope('not_hidden', function (Builder $builder) {
            $builder->where('hidden', 0)
                    ->whereHas('category', function ($q) {
                        $q->where('hidden', 0);
                    });
        });
    }

    public function getMerchantDescriptionAttribute(): string
    {
        $description = $this->description ?? '';

        // Decodifica &nbsp; &#039; &amp; ecc.
        $description = html_entity_decode(
            $description,
            ENT_QUOTES | ENT_HTML5,
            'UTF-8'
        );

        // Rimuove eventuali tag HTML
        $description = strip_tags($description);

        // Converte NBSP Unicode in spazio normale
        $description = str_replace("\xC2\xA0", ' ', $description);

        // Normalizza spazi, tab e a capo multipli
        $description = preg_replace('/\s+/u', ' ', $description);

        // Rimuove spazi iniziali/finali
        return trim($description);
    }

    public function getMerchantTitleAttribute(): string
    {
        $title = trim($this->name ?? '');

        // Normalizza spazi
        $title = preg_replace('/\s+/u', ' ', $title);

        // Se il titolo è completamente in maiuscolo,
        // lo converte in "Title case"
        if ($title === mb_strtoupper($title, 'UTF-8')) {
            $title = mb_convert_case(
                mb_strtolower($title, 'UTF-8'),
                MB_CASE_TITLE,
                'UTF-8'
            );
        }

        return $title;
    }

}

