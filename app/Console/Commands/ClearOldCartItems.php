<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ClearOldCartItems extends Command
{
    protected $signature = 'cart:clear-old';
    protected $description = 'Elimina cart items vecchi di 7 giorni e aggiorna lo stock in modo efficiente';

    public function handle()
    {
        $cutoff = Carbon::now()->subDays(7);

        DB::transaction(function () use ($cutoff) {
            // Aggiorna lo stock dei prodotti in modo aggregato
            $cartItems = DB::table('cart_items')
                ->select('product_id', DB::raw('SUM(quantity) as total_quantity'))
                ->where('updated_at', '<', $cutoff)
                ->groupBy('product_id')
                ->get();

            foreach ($cartItems as $item) {
                DB::table('products')
                    ->where('id', $item->product_id)
                    ->increment('stock', $item->total_quantity);
            }

            // Elimina i cart items vecchi
            DB::table('cart_items')
                ->where('updated_at', '<', $cutoff)
                ->delete();
        });

        $this->info('Cart items vecchi eliminati e stock aggiornato in modo efficiente.');
    }
}
