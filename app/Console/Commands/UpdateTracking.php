<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use App\Services\Track123Service;
use Illuminate\Support\Facades\Log;

class UpdateTracking extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-tracking';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Aggiorna lo stato delle spedizioni con Track123';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Avvio aggiornamento tracking...');

        $orders = Order::whereNotNull('tracking_number')
            ->where('status', '!=', 'delivered') // evita già consegnati
            ->get();

        $service = new Track123Service();

        foreach ($orders as $order) {

            try {

                $this->info("📦 Controllo ordine #{$order->id}");

                $status = $service->getTrackingStatus(
                    $order->tracking_number,
                    'poste-italiane' // rendilo dinamico se vuoi
                );

                if (!$status) {
                    $this->warn("⚠️ Nessun dato per tracking {$order->tracking_number}");
                    continue;
                }

                $newStatus = $this->mapStatus($status);

                // aggiorna solo se cambia
                if ($order->status !== $newStatus || $order->shipping_status !== $status) {

                    $order->update([
                        'status' => $newStatus,
                        'shipping_status' => $status
                    ]);

                    $this->info("✅ Ordine #{$order->id} → {$newStatus}");

                    Log::info("Tracking aggiornato", [
                        'order_id' => $order->id,
                        'tracking_number' => $order->tracking_number,
                        'track123_status' => $status,
                        'mapped_status' => $newStatus
                    ]);

                } else {
                    $this->line("⏭ Nessuna modifica ordine #{$order->id}");
                }

                // evita spam API
                sleep(1);

            } catch (\Exception $e) {

                $this->error("❌ Errore ordine #{$order->id}: " . $e->getMessage());

                Log::error("Errore tracking", [
                    'order_id' => $order->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        $this->info('🎉 Aggiornamento completato!');
    }

    /**
     * Mappa stato Track123 → stato ordine
     */
    private function mapStatus(?string $status): string
    {
        return match ($status) {
            'INIT', 'NO_RECORD' => 'processing',
            'INFO_RECEIVED' => 'processing',
            'IN_TRANSIT', 'WAITING_DELIVERY' => 'shipped',
            'DELIVERED' => 'delivered',
            default => 'pending',
        };
    }
}
