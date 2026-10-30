<?php

namespace App\Observers;

use App\Jobs\SendFarmacia19OrderPush;
use App\Models\Order;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Illuminate\Support\Facades\Log;

class OrderObserver implements ShouldHandleEventsAfterCommit
{
    public function created(Order $order): void
    {
        // Alla creazione, notifica solo gli ordini già pagati.
        if ($order->payment_status === 'paid') {
            $this->notify($order);
        }
    }

    public function updated(Order $order): void
    {
        Log::info('FARMACIA19 ORDER OBSERVER UPDATED', [
            'order_id' => $order->id,
            'status' => $order->status,
            'status_changed' => $order->wasChanged('status'),
            'payment_status' => $order->payment_status,
        ]);

        $statusChanged = $order->wasChanged('status');

        $becamePaid =
            $order->wasChanged('payment_status') &&
            $order->payment_status === 'paid';

        // Nessuna modifica rilevante.
        if (!$statusChanged && !$becamePaid) {
            return;
        }

        // Notifica anche gli ordini NON PAGATI
        // quando cambiano stato.
        if (
            $statusChanged &&
            in_array($order->status, [
                'processing',
                'shipped',
                'delivered',
                'cancelled',
            ], true)
        ) {
            $this->notify($order);
            return;
        }

        // Lo stato pending richiede il pagamento.
        if ($order->payment_status !== 'paid') {
            return;
        }

        // Per pending pagato o pagamento appena completato.
        $this->notify($order);
    }

    private function notify(Order $order): void
    {
        if (!$order->user_id) {
            return;
        }

        if (!in_array($order->status, [
            'pending',
            'processing',
            'shipped',
            'delivered',
            'cancelled',
        ], true)) {
            return;
        }

        SendFarmacia19OrderPush::dispatch(
            (int) $order->id,
            (string) $order->status,
            (int) $order->user_id
        );
    }
}