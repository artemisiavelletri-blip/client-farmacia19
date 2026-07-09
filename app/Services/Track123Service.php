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

        $this->client = new Client([
            'timeout' => 10,
        ]);
    }


    /**
     * Metodo principale
     */
    public function track(?string $trackingNumber, ?string $carrier = null): ?array
    {
        if (empty($trackingNumber)) {
            return [
                'tracking' => null,
                'status' => null
            ];
        }


        // 1) Prima provo a recuperare il tracking
        $data = $this->queryTracking($trackingNumber);


        // 2) Se trovato restituisco subito
        $result = $this->parseTracking($data);

        if ($result) {
            return $result;
        }


        // 3) Se non registrato lo creo
        if ($this->isNotRegistered($data)) {

            $created = $this->createTracking($trackingNumber, $carrier);

            if (!$created) {
                return null;
            }


            // Track123 impiega tempo ad indicizzarlo
            sleep(3);


            // 4) Riprovare la lettura
            for ($i = 0; $i < 3; $i++) {

                $data = $this->queryTracking($trackingNumber);

                $result = $this->parseTracking($data);

                if ($result) {
                    return $result;
                }

                sleep(2);
            }
        }


        return null;
    }



    /**
     * Query tracking
     */
    protected function queryTracking(string $trackingNumber): array
    {
        try {

            $response = $this->client->post(
                $this->baseUrl.'/track/query',
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

            Log::error('Track123 query error: '.$e->getMessage());

            return [];
        }
    }



    /**
     * Import tracking su Track123
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
                $this->baseUrl.'/track/import',
                [
                    'json' => [
                        'trackNoInfos' => [
                            $trackInfo
                        ]
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


            return empty($data['data']['rejected']);


        } catch (\Throwable $e) {

            Log::error('Track123 import error: '.$e->getMessage());

            return false;
        }
    }



    /**
     * Controllo tracking non registrato
     */
    protected function isNotRegistered(array $data): bool
    {
        return isset($data['data']['rejected'][0]['error']['code'])
            && $data['data']['rejected'][0]['error']['code'] === 'A0400';
    }



    /**
     * Estrazione dati tracking
     */
    protected function parseTracking(array $data): ?array
    {
        $content = $data['data']['accepted']['content'][0] ?? null;


        if (!$content) {
            return null;
        }


        return [
            'tracking' => $content['localLogisticsInfo']['trackingDetails'] ?? null,
            'status' => $content['transitStatus'] ?? null,
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