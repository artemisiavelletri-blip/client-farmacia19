<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use XMLWriter;

class GenerateGoogleMerchantFeed extends Command
{
    protected $signature = 'merchant:generate-feed';

    protected $description = 'Genera il feed XML Google Merchant';

    public function handle(): int
    {
        $directory = storage_path('app/feeds');

        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        /*
         * Scriviamo prima su un file temporaneo.
         * Google continuerà a leggere il vecchio feed
         * durante la generazione.
         */
        $tempPath = $directory.'/google-merchant.tmp.xml';
        $finalPath = $directory.'/google-merchant.xml';

        $xml = new XMLWriter();

        $xml->openURI($tempPath);
        $xml->startDocument('1.0', 'UTF-8');

        $xml->startElement('rss');
        $xml->writeAttribute('version', '2.0');
        $xml->writeAttribute(
            'xmlns:g',
            'http://base.google.com/ns/1.0'
        );

        $xml->startElement('channel');

        $xml->writeElement(
            'title',
            'Farmacia19'
        );

        $xml->writeElement(
            'link',
            'https://farmacia19.it'
        );

        $xml->writeElement(
            'description',
            'Catalogo prodotti Farmacia19'
        );

        $count = 0;

        Product::query()
            ->with('brandRelation')
            ->where('vet', 0)
            ->orderBy('id')
            ->chunkById(500, function ($products) use ($xml, &$count) {

                foreach ($products as $product) {

                    $xml->startElement('item');

                    /*
                     * ID
                     */
                    $xml->writeElement(
                        'g:id',
                        (string) $product->minsan
                    );

                    /*
                     * Titolo
                     */
                    $xml->writeElement(
                        'g:title',
                        $product->merchant_title
                    );

                    /*
                     * Descrizione
                     */
                    $xml->writeElement(
                        'g:description',
                        $product->merchant_description
                    );

                    /*
                     * Link prodotto
                     */
                    $xml->writeElement(
                        'g:link',
                        'https://farmacia19.it/shop-single/'
                        .$product->minsan
                    );

                    /*
                     * Prezzo
                     */
                    $xml->writeElement(
                        'g:price',
                        number_format(
                            $product->price,
                            2,
                            '.',
                            ''
                        ).' EUR'
                    );

                    /*
                     * Disponibilità
                     */
                    $xml->writeElement(
                        'g:availability',
                        $product->stock > 0
                            ? 'in_stock'
                            : 'out_of_stock'
                    );

                    /*
                     * Brand
                     */
                    if ($product->brandRelation) {
                        $xml->writeElement(
                            'g:brand',
                            $product->brandRelation->name
                        );
                    }

                    /*
                     * EAN / GTIN
                     */
                    if (!empty($product->ean)) {
                        $xml->writeElement(
                            'g:gtin',
                            $product->ean
                        );
                    }

                    $xml->endElement(); // item

                    $count++;
                }

                /*
                 * Forziamo la scrittura sul disco.
                 */
                $xml->flush();

                $this->info(
                    "Prodotti elaborati: {$count}"
                );
            });

        $xml->endElement(); // channel
        $xml->endElement(); // rss

        $xml->endDocument();
        $xml->flush();

        /*
         * Sostituzione atomica.
         *
         * Solo quando il nuovo XML è completamente pronto
         * sostituiamo quello precedente.
         */
        rename(
            $tempPath,
            $finalPath
        );

        $this->newLine();

        $this->info(
            "Feed Google Merchant generato: {$count} prodotti"
        );

        return self::SUCCESS;
    }
}