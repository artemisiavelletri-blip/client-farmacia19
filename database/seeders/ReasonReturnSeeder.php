<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReasonReturnSeeder extends Seeder
{
    public function run(): void
    {
        $reasons = [
            'Prodotto difettoso',
            'Articolo danneggiato con confezione esterna intatta',
            'Acquistato per errore',
            'Nessun motivo',
            'Incompatibile o inadeguato',
            'L\'articolo inviato è errato',
            'Data di consegna prevista non rispettata',
            'L\'articolo è difettoso o non funziona',
            'Non più necessario',
            'L\'articolo e la scatola di spedizione sono entrambi danneggiati'
        ];

        foreach ($reasons as $reason) {
            DB::table('reason_return')->insert([
                'reason' => $reason,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}