<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Product;
use App\Models\SubCategory;
use Illuminate\Console\Command;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

class SiteMapUpdate extends Command
{
    /**
     * Nome del comando Artisan.
     */
    protected $signature = 'app:site-map-update';

    /**
     * Descrizione del comando.
     */
    protected $description = 'Genera la sitemap XML di Farmacia19';

    /**
     * Dominio principale.
     */
    private string $baseUrl = 'https://farmacia19.it';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Generazione sitemap Farmacia19...');
        $this->newLine();

        $sitemap = Sitemap::create();

        /*
        |--------------------------------------------------------------------------
        | CONTATORI
        |--------------------------------------------------------------------------
        */

        $categoryCount = 0;
        $subCategoryCount = 0;
        $productCount = 0;

        /*
        |--------------------------------------------------------------------------
        | 1. HOMEPAGE
        |--------------------------------------------------------------------------
        */

        $sitemap->add(
            Url::create($this->baseUrl.'/')
        );

        /*
        |--------------------------------------------------------------------------
        | 2. CATEGORIE
        |--------------------------------------------------------------------------
        |
        | Category possiede già il Global Scope:
        |
        | hidden = 0
        |
        | quindi Category::query() esclude automaticamente
        | le categorie nascoste.
        |
        */

        $this->info('Aggiungo le categorie...');

        Category::query()
            ->select([
                'id',
                'token',
                'updated_at',
            ])
            ->whereNotNull('token')
            ->where('token', '!=', '')
            ->orderBy('id')
            ->chunkById(
                500,
                function ($categories) use (
                    $sitemap,
                    &$categoryCount
                ) {
                    foreach ($categories as $category) {

                        $url = Url::create(
                            $this->baseUrl
                            .'/shop-grid/'
                            .$category->token
                        );

                        /*
                         * Inseriamo lastmod solamente
                         * quando updated_at è disponibile.
                         */
                        if ($category->updated_at) {
                            $url->setLastModificationDate(
                                $category->updated_at
                            );
                        }

                        $sitemap->add($url);

                        $categoryCount++;
                    }
                }
            );

        $this->info(
            "Categorie aggiunte: {$categoryCount}"
        );

        /*
        |--------------------------------------------------------------------------
        | 3. SOTTOCATEGORIE
        |--------------------------------------------------------------------------
        |
        | SubCategory NON possiede hidden.
        |
        | whereHas('category') assicura che la categoria padre
        | sia visibile, perché Category applica il proprio
        | Global Scope hidden = 0.
        |
        | Controlliamo inoltre che esista almeno un prodotto
        | visibile associato alla sottocategoria.
        |
        */

        $this->info('Aggiungo le sottocategorie...');

        SubCategory::query()

            // Deve appartenere a una categoria visibile
            ->whereHas('category')

            // Deve avere almeno un prodotto indicizzabile
            ->whereExists(function ($query) {

                $query->selectRaw('1')
                    ->from('products')
                    ->join(
                        'category',
                        'category.id',
                        '=',
                        'products.category_id'
                    )
                    ->whereColumn(
                        'products.subcategory_id',
                        'subcategory.id'
                    )
                    ->where(
                        'products.hidden',
                        0
                    )
                    ->where(
                        'category.hidden',
                        0
                    );
            })

            ->select([
                'id',
                'category_id',
                'token',
                'updated_at',
            ])

            ->whereNotNull('token')
            ->where('token', '!=', '')

            ->orderBy('id')

            ->chunkById(
                500,
                function ($subCategories) use (
                    $sitemap,
                    &$subCategoryCount
                ) {
                    foreach ($subCategories as $subCategory) {

                        $url = Url::create(
                            $this->baseUrl
                            .'/shop-grid/'
                            .$subCategory->token
                        );

                        if ($subCategory->updated_at) {
                            $url->setLastModificationDate(
                                $subCategory->updated_at
                            );
                        }

                        $sitemap->add($url);

                        $subCategoryCount++;
                    }
                }
            );

        $this->info(
            "Sottocategorie aggiunte: {$subCategoryCount}"
        );

        /*
        |--------------------------------------------------------------------------
        | 4. PRODOTTI
        |--------------------------------------------------------------------------
        |
        | Product possiede già questo Global Scope:
        |
        | hidden = 0
        |
        | + categoria hidden = 0
        |
        | quindi Product::query() restituisce solamente
        | prodotti indicizzabili secondo queste regole.
        |
        */

        $this->info('Aggiungo i prodotti...');

        Product::query()

            ->select([
                'id',
                'minsan',
                'category_id',
                'updated_at',
            ])

            /*
             * Non possiamo creare una URL prodotto
             * senza MINSAN.
             */
            ->whereNotNull('minsan')
            ->where('minsan', '!=', '')

            ->orderBy('id')

            ->chunkById(
                1000,
                function ($products) use (
                    $sitemap,
                    &$productCount
                ) {
                    foreach ($products as $product) {

                        /*
                         * URL reale utilizzata da Farmacia19:
                         *
                         * /shop-single/{minsan}
                         */

                        $url = Url::create(
                            $this->baseUrl
                            .'/shop-single/'
                            .$product->minsan
                        );

                        if ($product->updated_at) {
                            $url->setLastModificationDate(
                                $product->updated_at
                            );
                        }

                        $sitemap->add($url);

                        $productCount++;
                    }
                }
            );

        $this->info(
            "Prodotti aggiunti: {$productCount}"
        );

        /*
        |--------------------------------------------------------------------------
        | 5. GENERAZIONE FILE
        |--------------------------------------------------------------------------
        */

        $path = public_path('sitemap.xml');

        $this->newLine();
        $this->info('Scrittura sitemap.xml...');

        $sitemap->writeToFile($path);

        /*
        |--------------------------------------------------------------------------
        | 6. RIEPILOGO
        |--------------------------------------------------------------------------
        */

        $total =
            1
            + $categoryCount
            + $subCategoryCount
            + $productCount;

        $this->newLine();

        $this->info(
            'Sitemap generata con successo!'
        );

        $this->newLine();

        $this->table(
            [
                'Tipo',
                'URL inseriti',
            ],
            [
                [
                    'Homepage',
                    1,
                ],
                [
                    'Categorie',
                    $categoryCount,
                ],
                [
                    'Sottocategorie',
                    $subCategoryCount,
                ],
                [
                    'Prodotti',
                    $productCount,
                ],
                [
                    'TOTALE',
                    $total,
                ],
            ]
        );

        $this->newLine();

        $this->info(
            "File generato: {$path}"
        );

        $this->info(
            'Sitemap pubblica: '
            .$this->baseUrl
            .'/sitemap.xml'
        );

        return self::SUCCESS;
    }
}