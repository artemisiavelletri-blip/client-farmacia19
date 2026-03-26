<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class TrackingMoreService
{
    protected $apiKey;
    protected $baseUrl;

    public function __construct()
    {
        $this->apiKey = '5cc9611b442f482ba8c0887d9c85a3fc';
        $this->baseUrl = 'https://api.trackingmore.com/v4';
    }

    /**
     * Traccia una spedizione
     */
    public function trackOrCreate(string $courierCode, string $trackingNumber, array $options = [])
    {
        // 1️⃣ Prova a leggere lo stato
        $response = Http::withHeaders([
            'Tracking-Api-Key' => $this->apiKey,
            'Content-Type' => 'application/json',
        ])->get("{$this->baseUrl}/trackings/get", [
            'courier_code' => strtolower(trim($courierCode)),
            'tracking_numbers' => trim($trackingNumber),
        ]);

        $data = $response->json();

        // 2️⃣ Se non esiste, errore 4102 → crea tracking
        if (isset($data['meta']['code']) && $data['meta']['code'] == 4102) {

            $data = [
                "tracking_number" => $trackingNumber,
                "courier_code"    => $courierCode
            ];

            $curl = curl_init();

            curl_setopt_array($curl, [
                CURLOPT_URL => "https://api.trackingmore.com/v4/trackings/create",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => [
                    "Accept: application/json",
                    "Content-Type: application/json",
                    "Tracking-Api-Key: $this->apiKey"
                ],
                CURLOPT_POSTFIELDS => json_encode($data),
                CURLOPT_TIMEOUT => 30,
            ]);

            $response = curl_exec($curl);
            $err = curl_error($curl);

            curl_close($curl);

            if (isset($createData['meta']['code']) && $createData['meta']['code'] == 200) {
                // Tracking creato con successo, puoi fare GET subito se vuoi
                return $createData;
            }

            return [
                'error' => $createData,
                'status' => $createResponse->status()
            ];
        }

        $data = $data['data'][0]['origin_info']['trackinfo'];
        return $data;
    }

    public function track(string $courierCode, $trackingNumber, array $options = [])
    {
        if(!$trackingNumber){
            return null;
        }

        $response = Http::withHeaders([
            'Tracking-Api-Key' => $this->apiKey,
            'Content-Type' => 'application/json',
        ])->get("{$this->baseUrl}/trackings/get", [
            'courier_code' => strtolower(trim($courierCode)),
            'tracking_numbers' => trim($trackingNumber),
        ]);

        $data = $response->json();

        if (isset($data['meta']['code']) && $data['meta']['code'] == 4102) {
            return [];
        }

        if (isset($createData['meta']['code']) && $createData['meta']['code'] == 200) {
            // Tracking creato con successo, puoi fare GET subito se vuoi
            return $createData;
        }
        dd($data);

        if(!$data['data']){
            return null;
        }

        $data = $data['data'][0]['origin_info']['trackinfo'];
        return $data;
    }
}