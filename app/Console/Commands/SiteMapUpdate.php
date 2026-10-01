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
     * Nome del comando Artisan
     */
    protected $signature = 'app:site-map-update';

    /**
     * Descrizione del comando
     */
    protected $description = 'Aggiorna la sitemap del sito Farmacia19';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Generazione sitemap Farmacia19...');

        $sitemap = Sitemap::create();

        $baseUrl = 'https://farmacia19.it';

        /*
        |--------------------------------------------------------------------------
        | HOMEPAGE
        |--------------------------------------------------------------------------
        */

        $sitemap->add(
            Url::create($baseUrl.'/')
                ->setPriority(1.0)
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
        );

        /*
        |--------------------------------------------------------------------------
        | CATEGORIE
        |--------------------------------------------------------------------------
        |
        | Category ha già il Global Scope:
        | hidden = 0
        |
        | quindi le categorie nascoste vengono automaticamente escluse.
        |
        */

        $categoryCount = 0;

        Category::query()
            ->select([
                'id',
                'token',
                'updated_at',
            ])
            ->orderBy('id')
            ->chunkById(500, function ($categories) use (
                $sitemap,
                $baseUrl,
                &$categoryCount
            ) {
                foreach ($categories as $category) {

                    if (empty($category->token)) {
                        continue;
                    }

                    $url = Url::create(
                        $baseUrl.'/shop-grid/'.$category->token
                    )
                        ->setPriority(0.9)
                        ->setChangeFrequency(
                            Url::CHANGE_FREQUENCY_DAILY
                        );

                    if ($category->updated_at) {
                        $url->setLastModificationDate(
                            $category->updated_at
                        );
                    }

                    $sitemap->add($url);

                    $categoryCount++;
                }
            });

        $this->info(
            "Categorie aggiunte: {$categoryCount}"
        );

        /*
        |--------------------------------------------------------------------------
        | SOTTOCATEGORIE
        |--------------------------------------------------------------------------
        |
        | SubCategory NON ha hidden.
        |
        | Usiamo whereHas('category') così vengono incluse solamente
        | sottocategorie appartenenti a categorie visibili.
        |
        | Il Global Scope di Category esclude automaticamente hidden = 1.
        |
        */

        $subCategoryCount = 0;

        SubCategory::query()
            ->whereHas('category')
            ->select([
                'id',
                'category_id',
                'token',
                'updated_at',
            ])
            ->orderBy('id')
            ->chunkById(500, function ($subCategories) use (
                $sitemap,
                $baseUrl,
                &$subCategoryCount
            ) {
                foreach ($subCategories as $subCategory) {

                    if (empty($subCategory->token)) {
                        continue;
                    }

                    $url = Url::create(
                        $baseUrl.'/shop-grid/'.$subCategory->token
                    )
                        ->setPriority(0.8)
                        ->setChangeFrequency(
                            Url::CHANGE_FREQUENCY_DAILY
                        );

                    if ($subCategory->updated_at) {
                        $url->setLastModificationDate(
                            $subCategory->updated_at
                        );
                    }

                    $sitemap->add($url);

                    $subCategoryCount++;
                }
            });

        $this->info(
            "Sottocategorie aggiunte: {$subCategoryCount}"
        );

        /*
        |--------------------------------------------------------------------------
        | PRODOTTI
        |--------------------------------------------------------------------------
        |
        | Product ha già un Global Scope che esclude:
        |
        | - prodotti hidden = 1
        | - prodotti appartenenti a Category hidden = 1
        |
        */

        $productCount = 0;

        Product::query()
            ->select([
                'id',
                'minsan',
                'category_id',
                'updated_at',
            ])
            ->whereNotNull('minsan')
            ->where('minsan', '!=', '')
            ->orderBy('id')
            ->chunkById(1000, function ($products) use (
                $sitemap,
                $baseUrl,
                &$productCount
            ) {
                foreach ($products as $product) {

                    $url = Url::create(
                        $baseUrl.'/shop-single/'.$product->minsan
                    )
                        ->setPriority(0.7)
                        ->setChangeFrequency(
                            Url::CHANGE_FREQUENCY_WEEKLY
                        );

                    if ($product->updated_at) {
                        $url->setLastModificationDate(
                            $product->updated_at
                        );
                    }

                    $sitemap->add($url);

                    $productCount++;
                }
            });

        $this->info(
            "Prodotti aggiunti: {$productCount}"
        );

        /*
        |--------------------------------------------------------------------------
        | SCRITTURA SITEMAP
        |--------------------------------------------------------------------------
        */

        $path = public_path('sitemap.xml');

        $sitemap->writeToFile($path);

        /*
        |--------------------------------------------------------------------------
        | RISULTATO
        |--------------------------------------------------------------------------
        */

        $total = 1
            + $categoryCount
            + $subCategoryCount
            + $productCount;

        $this->newLine();

        $this->info('Sitemap generata con successo!');

        $this->table(
            ['Tipo', 'URL'],
            [
                ['Homepage', 1],
                ['Categorie', $categoryCount],
                ['Sottocategorie', $subCategoryCount],
                ['Prodotti', $productCount],
                ['TOTALE', $total],
            ]
        );

        $this->newLine();

        $this->info("File: {$path}");
        $this->info(
            'URL: https://farmacia19.it/sitemap.xml'
        );

        return self::SUCCESS;
    }
}