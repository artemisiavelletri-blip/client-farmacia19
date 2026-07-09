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
        $this->apiKey = env('TRACK123_API_KEY');

        $this->baseUrl = 'https://api.track123.com/gateway/open-api/tk/v2.1';

        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'timeout' => 15,
            'headers' => [
                'Track123-Api-Secret' => $this->apiKey,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
        ]);
    }


    /**
     * Riconosce automaticamente il corriere
     */
    public function detectCourier(string $trackingNumber): ?string
    {
        try {

            $response = $this->client->post('/courier/detection', [
                'json' => [
                    'trackNo' => $trackingNumber
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            return data_get(
                $data,
                'data.courierCode'
            );

        } catch (\Throwable $e) {

            Log::error('Track123 Detection: '.$e->getMessage());

            return null;
        }
    }


    /**
     * Recupera tracking completo
     */
    public function track(string $trackingNumber): ?array
    {
        try {

            // 1) riconosce corriere
            $courierCode = $this->detectCourier($trackingNumber);


            if (!$courierCode) {

                Log::warning(
                    'Track123 corriere non trovato: '.$trackingNumber
                );

                return null;
            }


            // 2) richiesta tracking
            $response = $this->client->post('/track/query', [
                'json' => [
                    'trackNoInfos' => [
                        [
                            'trackNo' => $trackingNumber,
                            'courierCode' => $courierCode,
                        ],
                    ],
                ],
            ]);


            $data = json_decode(
                $response->getBody()->getContents(),
                true
            );


            if (!empty($data['data']['accepted']['content'][0])) {

                $content = $data['data']['accepted']['content'][0];


                return [
                    'courier' => $courierCode,

                    'tracking' =>
                        $content['localLogisticsInfo']['trackingDetails']
                        ?? null,

                    'status' =>
                        $content['transitStatus']
                        ?? null,
                ];
            }


            return null;


        } catch (\Throwable $e) {

            Log::error(
                'Track123 Error: '.$e->getMessage()
            );

            return null;
        }
    }


    /**
     * Solo stato
     */
    public function getTrackingStatus(string $trackingNumber): ?string
    {
        $tracking = $this->track($trackingNumber);

        return $tracking['status'] ?? null;
    }
}