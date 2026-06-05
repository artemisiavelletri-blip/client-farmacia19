<?php

namespace App\Services;

use Nexi\Npg\NpgClient;

class NexiService
{
    protected NpgClient $client;

    public function __construct()
    {
        $this->client = new NpgClient(
            apiKey: config('services.nexi.api_key'),
            baseUrl: config('services.nexi.base_url')
        );
    }

    public function createCardVerification($user)
    {
        return $this->client->orders()->create([
            'order' => [
                'amount' => 0,
                'currency' => 'EUR',
            ],
            'customer' => [
                'customerId' => (string)$user->id,
            ],
            'paymentSession' => [
                'actionType' => 'VERIFY',
            ],
        ]);
    }

    public function getOperation(string $operationId)
    {
        return $this->client->operations()->details($operationId);
    }
}