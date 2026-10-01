<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use XMLWriter;

class GenerateGoogleMerchantFeed extends Command
{
    protected $signature = 'merchant:generate-feed';

    protected $description = 'Genera il feed XML per Google Merchant';

    private string $baseUrl = 'https://farmacia19.it';

    public function handle(): int
    {
        $this->info('Generazione feed Google Merchant...');
        $this->newLine();

        /*
        |--------------------------------------------------------------------------
        | Percorsi
        |--------------------------------------------------------------------------
        */

        $directory = storage_path('app/feeds');

        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $tempPath = $directory . '/google-merchant.tmp.xml';
        $finalPath = $directory . '/google-merchant.xml';

        /*
        |--------------------------------------------------------------------------
        | XMLWriter
        |--------------------------------------------------------------------------
        */

        $xml = new XMLWriter();

        if (!$xml->openURI($tempPath)) {
            $this->error('Impossibile creare il file XML.');

            return self::FAILURE;
        }

        $xml->startDocument('1.0', 'UTF-8');

        /*
        |--------------------------------------------------------------------------
        | RSS
        |--------------------------------------------------------------------------
        */

        $xml->startElement('rss');

        $xml->writeAttribute(
            'xmlns:g',
            'http://base.google.com/ns/1.0'
        );

        $xml->writeAttribute('version', '2.0');

        /*
        |--------------------------------------------------------------------------
        | CHANNEL
        |--------------------------------------------------------------------------
        */

        $xml->startElement('channel');

        $xml->writeElement(
            'title',
            'Farmacia19'
        );

        $xml->writeElement(
            'link',
            $this->baseUrl . '/'
        );

        $xml->writeElement(
            'description',
            'Catalogo prodotti Farmacia19'
        );

        /*
        |--------------------------------------------------------------------------
        | Prodotti
        |--------------------------------------------------------------------------
        */

        $count = 0;

        Product::query()

            ->with('brandRelation')

            // hidden è già gestito dal Global Scope del model Product

            ->where('vet', 0)

            ->select([
                'id',
                'minsan',
                'ean',
                'name',
                'description',
                'image',
                'stock',
                'price',
                'discountPrice',
                'brand_id',
                'category_id',
            ])

            ->orderBy('id')

            ->chunkById(
                500,
                function ($products) use ($xml, &$count) {

                    foreach ($products as $product) {

                        /*
                        |--------------------------------------------------------------------------
                        | ITEM
                        |--------------------------------------------------------------------------
                        */

                        $xml->startElement('item');

                        /*
                        |--------------------------------------------------------------------------
                        | ID
                        |--------------------------------------------------------------------------
                        */

                        $xml->writeElement(
                            'g:id',
                            (string) $product->minsan
                        );

                        /*
                        |--------------------------------------------------------------------------
                        | TITLE
                        |--------------------------------------------------------------------------
                        */

                        $xml->startElement('g:title');

                        $xml->writeCData(
                            $product->merchant_title ?? ''
                        );

                        $xml->endElement();

                        /*
                        |--------------------------------------------------------------------------
                        | DESCRIPTION
                        |--------------------------------------------------------------------------
                        */

                        $xml->startElement('g:description');

                        $xml->writeCData(
                            $product->merchant_description ?? ''
                        );

                        $xml->endElement();

                        /*
                        |--------------------------------------------------------------------------
                        | LINK
                        |--------------------------------------------------------------------------
                        */

                        $productIdentifier = !empty($product->minsan)
                            ? $product->minsan
                            : $product->ean;

                        $xml->writeElement(
                            'g:link',
                            $this->baseUrl
                            . '/shop-single/'
                            . $productIdentifier
                        );

                        /*
                        |--------------------------------------------------------------------------
                        | IMAGE
                        |--------------------------------------------------------------------------
                        */

                        $imageUrl = $this->getProductImageUrl(
                            $product->image
                        );

                        $xml->writeElement(
                            'g:image_link',
                            $imageUrl
                        );

                        /*
                        |--------------------------------------------------------------------------
                        | AVAILABILITY
                        |--------------------------------------------------------------------------
                        */

                        $xml->writeElement(
                            'g:availability',
                            $product->stock > 0
                                ? 'in_stock'
                                : 'out_of_stock'
                        );

                        /*
                        |--------------------------------------------------------------------------
                        | PRICE
                        |--------------------------------------------------------------------------
                        */

                        if (!$product->discountPrice) {
                            $price = $product->price;
                        } else {
                            $price = $product->discountPrice;
                        }

                        $price = number_format(
                            $price,
                            2,
                            '.',
                            ''
                        );

                        $xml->writeElement(
                            'g:price',
                            $price . ' EUR'
                        );

                        /*
                        |--------------------------------------------------------------------------
                        | CONDITION
                        |--------------------------------------------------------------------------
                        */

                        $xml->writeElement(
                            'g:condition',
                            'new'
                        );

                        /*
                        |--------------------------------------------------------------------------
                        | BRAND
                        |--------------------------------------------------------------------------
                        */

                        if ($product->brandRelation) {

                            $xml->startElement('g:brand');

                            $xml->writeCData(
                                $product->brandRelation->name
                            );

                            $xml->endElement();
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | GTIN / EAN
                        |--------------------------------------------------------------------------
                        */

                        if (!empty($product->ean)) {

                            $xml->writeElement(
                                'g:gtin',
                                (string) $product->ean
                            );
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | Fine ITEM
                        |--------------------------------------------------------------------------
                        */

                        $xml->endElement();

                        $count++;
                    }

                    /*
                     * Scrive il buffer sul disco.
                     */
                    $xml->flush();

                    $this->info(
                        "Prodotti elaborati: {$count}"
                    );
                }
            );

        /*
        |--------------------------------------------------------------------------
        | Chiusura XML
        |--------------------------------------------------------------------------
        */

        $xml->endElement(); // channel
        $xml->endElement(); // rss

        $xml->endDocument();
        $xml->flush();

        /*
        |--------------------------------------------------------------------------
        | Sostituzione atomica
        |--------------------------------------------------------------------------
        */

        if (!rename($tempPath, $finalPath)) {

            $this->error(
                'Errore durante la sostituzione del feed.'
            );

            return self::FAILURE;
        }

        /*
        |--------------------------------------------------------------------------
        | Risultato
        |--------------------------------------------------------------------------
        */

        $this->newLine();

        $this->info(
            'Feed Google Merchant generato con successo!'
        );

        $this->table(
            ['Dato', 'Valore'],
            [
                ['Prodotti', $count],
                ['File', $finalPath],
            ]
        );

        return self::SUCCESS;
    }

    /**
     * Restituisce l'immagine del prodotto oppure
     * l'immagine "file non disponibile".
     */
    private function getProductImageUrl(?string $image): string
    {
        $allowedExtensions = [
            'jpg',
            'jpeg',
            'png',
            'gif',
        ];

        if (!empty($image)) {

            $extension = strtolower(
                pathinfo(
                    $image,
                    PATHINFO_EXTENSION
                )
            );

            $validExtension = in_array(
                $extension,
                $allowedExtensions,
                true
            );

            $fileExists = file_exists(
                public_path(
                    'storage-admin/' . $image
                )
            );

            if ($validExtension && $fileExists) {

                return $this->baseUrl
                    . '/storage-admin/'
                    . ltrim($image, '/');
            }
        }

        return $this->baseUrl
            . '/storage-admin/products/file-non-disponibile.jpg';
    }
}