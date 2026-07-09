<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class Track123Service
{
    protected string $apiKey;
    protected string $baseUrl;
    protected Client $client;

    public function __construct()
    {
        $this->apiKey = env('TRACK123_API_KEY', '45426f8ea43542ce9094303445b12160');
        $this->baseUrl = 'https://api.track123.com/gateway/open-api/tk/v2.1';

        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'timeout' => 10,
            'headers' => [
                'Track123-Api-Secret' => $this->apiKey,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
        ]);
    }

    /**
     * Restituisce tracking e stato
     */
    public function track(?string $trackingNumber): ?array
    {
        if (empty($trackingNumber)) {
            return [
                'tracking' => null,
                'status' => null,
            ];
        }

        try {

            for ($i = 0; $i < 3; $i++) {

                $response = $this->client->post('/track/query', [
                    'json' => [
                        'trackNoInfos' => [
                            [
                                'trackNo' => $trackingNumber,
                            ],
                        ],
                    ],
                ]);

                $data = json_decode($response->getBody()->getContents(), true);

                if (!empty($data['data']['rejected'])) {
                    return null;
                }

                if (!empty($data['data']['accepted']['content'][0])) {

                    $content = $data['data']['accepted']['content'][0];

                    return [
                        'tracking' => $content['localLogisticsInfo']['trackingDetails'] ?? null,
                        'status'   => $content['transitStatus'] ?? null,
                    ];
                }

                // Track123 potrebbe non aver ancora elaborato il tracking
                sleep(2);
            }

            return null;

        } catch (\Throwable $e) {

            Log::error('Track123: '.$e->getMessage());

            return null;
        }
    }

    /**
     * Restituisce solo lo stato della spedizione
     */
    public function getTrackingStatus(string $trackingNumber): ?string
    {
        $tracking = $this->track($trackingNumber);

        return $tracking['status'] ?? null;
    }
}