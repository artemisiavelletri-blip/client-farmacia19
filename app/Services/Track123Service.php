<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class Track123Service
{
    protected $apiKey;
    protected $baseUrl;

    public function __construct()
    {
        $this->apiKey = '45426f8ea43542ce9094303445b12160';
        $this->baseUrl = 'https://api.track123.com/gateway/open-api/tk/v2.1';
    }

    /**
     * Metodo principale: crea tracking se non esiste e restituisce dati
     */
    public function track(string $trackingNumber, ?string $carrier = null): ?array
    {

        $response = $client->request('POST', $this->baseUrl . '/track/query', [
          'body' => '{"trackNoInfos":[{"trackNo": "' . $trackingNumber . '"}]}',
          'headers' => [
            'Track123-Api-Secret' => $this->apiKey,
            'accept' => 'application/json',
            'content-type' => 'application/json',
          ],
        ]);

        dd($response->getBody()
        // 1️⃣ crea il tracking
        $this->createTracking($trackingNumber, $carrier);
        //dd($this);

        // 2️⃣ recupera i dati aggiornati
        return $this->getTracking($trackingNumber);
    }

    /**
     * Crea tracking su Track123
     */
    protected function createTracking(string $trackingNumber, ?string $carrier = null): void
    {
        try {

            $payload = [
                'tracking_number' => $trackingNumber,
            ];

            if ($carrier) {
                $payload['carrier_code'] = $carrier;
            }

            $this->client->post('trackings', [
                'json' => $payload
            ]);

        } catch (\Exception $e) {
            Log::warning("Track123 create tracking failed for {$trackingNumber}: " . $e->getMessage());
        }
    }

    /**
     * Recupera lo stato del tracking
     */
    protected function getTracking(string $trackingNumber): ?array
    {
        try {
            $response = $this->client->post('track', [
                'json' => [
                    'tracking_number' => $trackingNumber
                ]
            ]);
            dd('prova');
            $data = json_decode($response->getBody()->getContents(), true);

            return $data['data'] ?? null;

        } catch (\Exception $e) {
            Log::error("Track123 get tracking failed for {$trackingNumber}: " . $e->getMessage());
            return null;
        }
    }
}