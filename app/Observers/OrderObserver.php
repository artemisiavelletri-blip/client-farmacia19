<?php

namespace App\Observers;

use App\Jobs\SendFarmacia19OrderPush;
use App\Models\Order;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class OrderObserver implements ShouldHandleEventsAfterCommit
{
    public function created(Order $order): void
    {
        if ($order->payment_status === 'paid') {
            $this->notify($order);
        }
    }

    public function updated(Order $order): void
    {
        \Log::info('FARMACIA19 ORDER OBSERVER UPDATED', [
            'order_id' => $order->id,
            'status' => $order->status,
            'status_changed' => $order->wasChanged('status'),
            'payment_status' => $order->payment_status,
        ]);
        $statusChanged = $order->wasChanged('status');

        $becamePaid =
            $order->wasChanged('payment_status') &&
            $order->payment_status === 'paid';

        if (!$statusChanged && !$becamePaid) {
            return;
        }

        // Una cancellazione può essere comunicata anche
        // se il pagamento non è stato completato.
        if ($order->status === 'cancelled') {
            if ($statusChanged) {
                $this->notify($order);
            }

            return;
        }

        // Non confermare ordini non ancora pagati.
        if ($order->payment_status !== 'paid') {
            return;
        }

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