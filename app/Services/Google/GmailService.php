<?php

namespace App\Services\Google;

use Google\Client;
use Google\Service\Gmail;

class GmailService
{
    private Client $client;

    public function __construct()
    {
        $this->client = new Client();

        $this->client->setAuthConfig(
            storage_path('app/google/client_secret.json')
        );
    }

    /**
     * Inizializza client con token da .env
     */
    public function getClient(): Client
    {
        $accessToken = env('GOOGLE_ACCESS_TOKEN');
        $refreshToken = env('GOOGLE_REFRESH_TOKEN');

        $this->client->setAccessToken([
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
        ]);

        // 🔄 refresh automatico
        if ($this->client->isAccessTokenExpired()) {

            $newToken = $this->client->fetchAccessTokenWithRefreshToken(
                $refreshToken
            );

            $accessToken = $newToken['access_token'];

            // aggiorna client
            $this->client->setAccessToken($newToken);

            // ⚠️ opzionale: aggiorna .env (se vuoi)
            $this->updateEnvTokens($newToken);
        }

        return $this->client;
    }

    /**
     * Invia email Gmail API
     */
    public function sendEmail(string $to, string $subject, string $view, array $data = []): bool
    {
        $client = $this->getClient();
        $service = new Gmail($client);

        // 👇 QUI
        $html = \Illuminate\Support\Facades\View::make($view, $data)->render();

        $boundary = uniqid('np');

        $messageText =
            "To: {$to}\r\n" .
            "Subject: {$subject}\r\n" .
            "MIME-Version: 1.0\r\n" .
            "Content-Type: multipart/alternative; boundary=\"{$boundary}\"\r\n\r\n" .

            "--{$boundary}\r\n" .
            "Content-Type: text/html; charset=UTF-8\r\n\r\n" .
            $html . "\r\n\r\n" .

            "--{$boundary}--";

        $rawMessage = rtrim(
            strtr(base64_encode($messageText), '+/', '-_'),
            '='
        );

        $message = new \Google\Service\Gmail\Message();
        $message->setRaw($rawMessage);

        $service->users_messages->send("me", $message);

        return true;
    }

    /**
     * Aggiorna .env (opzionale ma utile)
     */
    private function updateEnvTokens(array $token)
    {
        $path = base_path('.env');
        $env = file_get_contents($path);

        $env = preg_replace(
            '/GOOGLE_ACCESS_TOKEN=.*/',
            'GOOGLE_ACCESS_TOKEN=' . $token['access_token'],
            $env
        );

        if (isset($token['refresh_token'])) {
            $env = preg_replace(
                '/GOOGLE_REFRESH_TOKEN=.*/',
                'GOOGLE_REFRESH_TOKEN=' . $token['refresh_token'],
                $env
            );
        }

        file_put_contents($path, $env);
    }
}