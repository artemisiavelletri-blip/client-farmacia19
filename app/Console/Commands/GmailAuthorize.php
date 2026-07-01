<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Google\Client;

class GmailAuthorize extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:gmail-authorize';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $client = new Client();

        $client->setAuthConfig(storage_path('app/google/client_secret.json'));

        $client->setAccessType('offline');

        $client->setPrompt('consent');

        $client->setRedirectUri('https://farmacia19.it/oauth/google/callback');

        $client->addScope(\Google\Service\Gmail::GMAIL_SEND);

        $url = $client->createAuthUrl();

        $this->info($url);
    }
}
