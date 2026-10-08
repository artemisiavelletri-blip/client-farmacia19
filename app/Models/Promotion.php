<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Promotion extends Model
{
    use HasFactory;

    /**
     * Tabella associata al model.
     */
    protected $table = 'discount';


    /**
     * Campi assegnabili.
     */
    protected $fillable = [
        'token',
        'name',
        'description',

        'all_products',

        'product_id',
        'category_id',
        'subcategory_id',
        'brand_id',

        'percentage',
        'fixDiscount',

        'start_date',
        'end_date',

        'minimum_purchase',
        'max_use',

        'user',
        'active',
    ];


    /**
     * Cast automatici.
     */
    protected $casts = [
        'all_products'     => 'boolean',
        'active'           => 'boolean',

        'percentage'       => 'decimal:2',
        'fixDiscount'      => 'decimal:2',
        'minimum_purchase' => 'decimal:2',

        'start_date'       => 'date',
        'end_date'         => 'date',

        'max_use'          => 'integer',
    ];


    /* =========================================================
       RELAZIONI
    ========================================================= */


    /**
     * Prodotto specifico associato al coupon.
     */
    public function product()
    {
        return $this->belongsTo(
            Product::class,
            'product_id'
        );
    }


    /**
     * Brand associato al coupon.
     */
    public function brand()
    {
        return $this->belongsTo(
            Brand::class,
            'brand_id'
        );
    }


    /**
     * Categoria associata al coupon.
     */
    public function category()
    {
        return $this->belongsTo(
            Category::class,
            'category_id'
        );
    }


    /**
     * Sottocategoria associata al coupon.
     */
    public function subcategory()
    {
        return $this->belongsTo(
            Subcategory::class,
            'subcategory_id'
        );
    }


    /**
     * Elementi del carrello ai quali è stato applicato
     * questo coupon.
     */
    public function cartItems()
    {
        return $this->belongsToMany(
            CartItem::class,
            'cart_item_discount',
            'discount_id',
            'cart_item_id'
        );
    }


    /* =========================================================
       VALIDITÀ COUPON
    ========================================================= */

    /**
     * Verifica se il coupon è attualmente valido.
     */
    public function isValid(): bool
    {
        if (!$this->active) {
            return false;
        }


        $today = now()->startOfDay();


        /*
         * Non ancora iniziato.
         */
        if (
            $this->start_date &&
            $this->start_date->startOfDay()->gt($today)
        ) {
            return false;
        }


        /*
         * Scaduto.
         */
        if (
            $this->end_date &&
            $this->end_date->endOfDay()->lt($today)
        ) {
            return false;
        }


        return true;
    }


    /* =========================================================
       APPLICABILITÀ AL PRODOTTO
    ========================================================= */

    /**
     * Controlla se questo coupon è applicabile
     * a un determinato prodotto.
     */
    public function appliesToProduct(Product $product): bool
    {
        /*
         * Tutti i prodotti.
         */
        if ($this->all_products) {
            return true;
        }


        /*
         * Prodotto specifico.
         */
        if (
            $this->product_id &&
            (int) $this->product_id === (int) $product->id
        ) {
            return true;
        }


        /*
         * Brand.
         */
        if (
            $this->brand_id &&
            (int) $this->brand_id === (int) $product->brand_id
        ) {
            return true;
        }


        /*
         * Categoria + sottocategoria.
         *
         * Se sono presenti entrambe, il prodotto
         * deve appartenere a entrambe.
         */
        if (
            $this->category_id &&
            $this->subcategory_id
        ) {
            return
                (int) $this->category_id === (int) $product->category_id
                &&
                (int) $this->subcategory_id === (int) $product->subcategory_id;
        }


        /*
         * Solo sottocategoria.
         */
        if (
            $this->subcategory_id &&
            (int) $this->subcategory_id === (int) $product->subcategory_id
        ) {
            return true;
        }


        /*
         * Solo categoria.
         */
        if (
            $this->category_id &&
            (int) $this->category_id === (int) $product->category_id
        ) {
            return true;
        }


        return false;
    }


    /* =========================================================
       APPLICATION TYPE
    ========================================================= */

    /**
     * Restituisce il tipo di applicazione da utilizzare
     * nella card e nella modal.
     *
     * Possibili valori:
     *
     * all_products
     * product
     * brand
     * category
     */
    public function getApplicationTypeAttribute(): ?string
    {
        /*
         * Tutti i prodotti.
         */
        if ($this->all_products) {
            return 'all_products';
        }


        /*
         * Prodotto specifico.
         */
        if ($this->product_id) {
            return 'product';
        }


        /*
         * Brand.
         */
        if ($this->brand_id) {
            return 'brand';
        }


        /*
         * Categoria e/o sottocategoria.
         *
         * Anche quando esiste una sottocategoria
         * restituiamo "category", perché graficamente
         * vogliamo mostrare sempre:
         *
         * Categoria
         * Nome categoria / Nome sottocategoria
         */
        if (
            $this->category_id ||
            $this->subcategory_id
        ) {
            return 'category';
        }


        return null;
    }


    /* =========================================================
       APPLICATION LABEL
    ========================================================= */

    /**
     * Restituisce il testo da visualizzare sotto
     * Prodotto / Brand / Categoria / Valido su.
     */
    public function getApplicationLabelAttribute(): ?string
    {
        /*
         * =====================================================
         * TUTTI I PRODOTTI
         * =====================================================
         */

        if ($this->all_products) {
            return 'Tutti i prodotti';
        }


        /*
         * =====================================================
         * PRODOTTO
         * =====================================================
         */

        if ($this->product_id) {

            if ($this->product) {

                /*
                 * Se il campo del prodotto nel tuo DB
                 * si chiama diversamente da "name",
                 * cambia questa riga.
                 */
                return $this->product->name;
            }

            return null;
        }


        /*
         * =====================================================
         * BRAND
         * =====================================================
         */

        if ($this->brand_id) {

            if ($this->brand) {

                return $this->brand->name;
            }

            return null;
        }


        /*
         * =====================================================
         * CATEGORIA + SOTTOCATEGORIA
         * =====================================================
         */

        if (
            $this->category_id &&
            $this->subcategory_id
        ) {

            $categoryName =
                $this->category
                    ? $this->category->name
                    : null;


            $subcategoryName =
                $this->subcategory
                    ? $this->subcategory->name
                    : null;


            /*
             * Entrambe disponibili.
             *
             * Esempio:
             *
             * Viso / Idratazione
             */
            if (
                $categoryName &&
                $subcategoryName
            ) {

                return
                    $categoryName
                    .
                    ' / '
                    .
                    $subcategoryName;
            }


            /*
             * Abbiamo trovato solo la categoria.
             */
            if ($categoryName) {
                return $categoryName;
            }


            /*
             * Abbiamo trovato solo la sottocategoria.
             */
            if ($subcategoryName) {
                return $subcategoryName;
            }


            return null;
        }


        /*
         * =====================================================
         * SOLO CATEGORIA
         * =====================================================
         */

        if ($this->category_id) {

            return $this->category
                ? $this->category->name
                : null;
        }


        /*
         * =====================================================
         * SOLO SOTTOCATEGORIA
         * =====================================================
         *
         * Manteniamo questo caso per sicurezza.
         *
         * application_type sarà comunque "category".
         */

        if ($this->subcategory_id) {

            return $this->subcategory
                ? $this->subcategory->name
                : null;
        }


        return null;
    }
}