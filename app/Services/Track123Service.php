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
    public function track(string $trackingNumber = null, ?string $carrier = null): ?array
    {

        if(!$trackingNumber){
            return null;
        }
        
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
        if(empty($data['data']['rejected'])){
            if (isset($data['data']['accepted']['content'][0]['localLogisticsInfo']['trackingDetails'])) {
                return ['tracking' => $data['data']['accepted']['content'][0]['localLogisticsInfo']['trackingDetails'],'status' => $data['data']['accepted']['content'][0]['transitStatus']];
            } else {
                return ['tracking' => null,'status' => $data['data']['accepted']['content'][0]['transitStatus']];
            }
        } else {
            return $this->createTracking($trackingNumber,$carrier);
        }
    }

    /**
     * Crea tracking su Track123
     */
    protected function createTracking(string $trackingNumber, ?string $carrier = null): ?array
    {
        try {

            $client = new \GuzzleHttp\Client();

            $response = $client->request('POST', $this->baseUrl . '/track/import', [
              'body' => '[{"trackNo":"' . $trackingNumber . '"}]',
              'headers' => [
                'Track123-Api-Secret' => $this->apiKey,
                'accept' => 'application/json',
                'content-type' => 'application/json',
              ],
            ]);

            $data = json_decode($response->getBody(), true); // true = array

            if (isset($data['data']['accepted']['content'][0]['localLogisticsInfo']['trackingDetails'])) {
                return ['tracking' => $data['data']['accepted']['content'][0]['localLogisticsInfo']['trackingDetails'],'status' => $data['data']['accepted']['content'][0]['transitStatus']];
            } else {
                return ['tracking' => null,'status' => $data['data']['accepted']['content'][0]['transitStatus']];
            }

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
            

        } catch (\Exception $e) {
            Log::error("Track123 get tracking failed for {$trackingNumber}: " . $e->getMessage());
            return null;
        }
    }
}