<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\Farmacia19PushService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendFarmacia19OrderPush implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public int $timeout = 30;

    public function __construct(
        public int $orderId,
        public string $status,
        public int $userId
    ) {
        $this->afterCommit();
    }

    public function handle(Farmacia19PushService $push): void
    {
        $order = Order::find($this->orderId);

        if (!$order || (int) $order->user_id !== $this->userId) {
            return;
        }

        // Non inviare notifiche obsolete se lo stato è già cambiato.
        if ($order->status !== $this->status) {
            return;
        }

        // Non confermare ordini che non risultano pagati.
        if (
            $this->status === 'pending' &&
            $order->payment_status !== 'paid'
        ) {
            return;
        }

        [$title, $body] = match ($this->status) {
            'pending' => [
                '📦 Ordine ricevuto',
                'Abbiamo ricevuto il tuo ordine Farmacia19.',
            ],
            'processing' => [
                '💚 Ordine in preparazione',
                'Stiamo preparando il tuo ordine.',
            ],
            'shipped' => [
                '🚚 Ordine spedito',
                'Il tuo ordine è stato spedito!',
            ],
            'delivered' => [
                '✅ Ordine consegnato',
                'Il tuo ordine risulta consegnato.',
            ],
            'cancelled' => [
                '❌ Ordine annullato',
                'Il tuo ordine è stato annullato.',
            ],
            default => [null, null],
        };

        if ($title === null) {
            return;
        }

        try {
            $push->sendToUser(
                $this->userId,
                $title,
                $body,
                [
                    'type' => 'order',
                    'order_id' => (string) $order->id,
                    'status' => $this->status,
                ]
            );
        } catch (Throwable $e) {
            Log::warning('Farmacia19: errore invio notifica ordine', [
                'order_id' => $this->orderId,
                'error_class' => get_class($e),
            ]);
        }
    }
}