<?php

namespace App\Services;

use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Core\ProductionEnvironment;
use PayPalCheckoutSdk\Core\PayPalHttpClient;

class PayPalService
{
    private PayPalHttpClient $client;

    public function __construct()
    {
        $env = config('services.paypal.mode') === 'live'
            ? new ProductionEnvironment(
                config('services.paypal.client_id'),
                config('services.paypal.secret')
            )
            : new SandboxEnvironment(
                config('services.paypal.client_id'),
                config('services.paypal.secret')
            );

        $this->client = new PayPalHttpClient($env);
    }

    public function getClient(): PayPalHttpClient
    {
        return $this->client;
    }
}