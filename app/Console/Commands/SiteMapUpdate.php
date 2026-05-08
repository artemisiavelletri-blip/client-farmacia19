<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SiteMapUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:site-map-update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Aggiorna la site map del sito';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        \Spatie\Sitemap\SitemapGenerator::create('https://farmacia19.it')
            ->writeToFile(public_path('sitemap.xml'));
    }
}
