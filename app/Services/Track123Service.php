<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class Track123Service
{
    protected $apiKey;
    protected $baseUrl;
    protected $client;

    public function __construct()
    {
        $this->apiKey = env('TRACK123_API_KEY', '45426f8ea43542ce9094303445b12160');
        $this->baseUrl = 'https://api.track123.com/gateway/open-api/tk/v2.1';

        $this->client = new \GuzzleHttp\Client([
            'timeout' => 10,
        ]);
    }


    public function track(?string $trackingNumber, ?string $carrier = null): ?array
    {
        if (empty($trackingNumber)) {
            return [
                'tracking' => null,
                'status' => null
            ];
        }


        // Prima provo a cercare il tracking
        $data = $this->queryTracking($trackingNumber);


        // Se già presente ritorno i dati
        $result = $this->parseTracking($data);

        if ($result) {
            return $result;
        }


        // Se non registrato provo ad importarlo
        if ($this->isNotRegistered($data)) {

            if (!$this->createTracking($trackingNumber, $carrier)) {
                return null;
            }


            // Tempo necessario a Track123 per elaborare
            sleep(5);


            // Riprovo fino a 3 volte
            for ($i = 0; $i < 3; $i++) {

                $data = $this->queryTracking($trackingNumber);

                $result = $this->parseTracking($data);

                if ($result) {
                    return $result;
                }

                sleep(3);
            }
        }


        return null;
    }


    /**
     * Recupera tracking da Track123
     */
    protected function queryTracking(string $trackingNumber): array
    {
        try {

            $response = $this->client->post(
                $this->baseUrl . '/track/query',
                [
                    'json' => [
                        'trackNoInfos' => [
                            [
                                'trackNo' => $trackingNumber
                            ]
                        ]
                    ],
                    'headers' => [
                        'Track123-Api-Secret' => $this->apiKey,
                        'accept' => 'application/json',
                        'content-type' => 'application/json',
                    ],
                ]
            );


            return json_decode(
                $response->getBody()->getContents(),
                true
            );


        } catch (\Throwable $e) {

            \Log::error('Track123 query error: '.$e->getMessage());

            return [];
        }
    }


    /**
     * Registra tracking su Track123
     */
    protected function createTracking(string $trackingNumber, ?string $carrier = null): bool
    {
        try {

            $trackInfo = [
                'trackNo' => $trackingNumber
            ];


            if ($carrier) {
                $trackInfo['courierCode'] = $carrier;
            }


            $response = $this->client->post(
                'https://api.track123.com/gateway/open-api/tk/v2/track/import',
                [
                    'json' => [
                        $trackInfo
                    ],
                    'headers' => [
                        'Track123-Api-Secret' => $this->apiKey,
                        'accept' => 'application/json',
                        'content-type' => 'application/json',
                    ],
                ]
            );


            $data = json_decode(
                $response->getBody()->getContents(),
                true
            );


            \Log::info('Track123 import response', $data);


            return empty($data['data']['rejected']);


        } catch (\Throwable $e) {

            \Log::error('Track123 import error: '.$e->getMessage());

            return false;
        }
    }


    /**
     * Controlla se Track123 dice "non registrato"
     */
    protected function isNotRegistered(array $data): bool
    {
        return isset($data['data']['rejected'][0]['error']['code'])
            && $data['data']['rejected'][0]['error']['code'] === 'A0400';
    }


    /**
     * Estrae dati tracking
     */
    protected function parseTracking(array $data): ?array
    {
        $content = $data['data']['accepted']['content'][0] ?? null;


        if (!$content) {
            return null;
        }


        return [
            'tracking' => $content['localLogisticsInfo']['trackingDetails'] ?? null,
            'status' => $content['transitStatus'] ?? null
        ];
    }

    public function getTrackingStatus(string $trackingNumber, string $carrier): ?string
    {
        try {
            $client = new \GuzzleHttp\Client();

            $response = $client->request('POST', $this->baseUrl . '/track/query', [
              'body' => '{"trackNoInfos":[{"trackNo":"' . $trackingNumber . '"}]}',
              'headers' => [
                'Track123-Api-Secret' => $this->apiKey,
                'accept' => 'application/json',
                'content-type' => 'application/json',
              ],
            ]);

           $data = json_decode($response->getBody(), true); // true = array

            return $data['data']['accepted']['content'][0]['transitStatus'] ?? null;

        } catch (\Exception $e) {
            \Log::error('Track123 error: ' . $e->getMessage());
            return null;
        }
    }
}