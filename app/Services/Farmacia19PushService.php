<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Throwable;

class Farmacia19PushService
{
    /**
     * Invia ai dispositivi registrati di un utente.
     * Chiamare solo dopo il commit della transazione dell'evento applicativo.
     * Non includere dati sanitari, indirizzi o dettagli sensibili nel testo push.
     */
    public function sendToUser(int $userId, string $title, string $body, array $data = []): int
    {
        $tokens = DB::table('farmacia19_fcm_tokens')
            ->where('user_id', $userId)
            ->pluck('token');

        $sent = 0;
        foreach ($tokens as $token) {
            try {
                $message = CloudMessage::withTarget('token', $token)
                    ->withNotification(Notification::create($title, $body))
                    ->withData(array_map(static fn ($value) => (string) $value, $data));

                app('firebase.messaging')->send($message);
                $sent++;
            } catch (Throwable $e) {
                Log::warning('Farmacia19 FCM: invio fallito', [
                    'user_id' => $userId,
                    'error_class' => get_class($e),
                    // Evitare di registrare il token o il messaggio completo nei log.
                ]);
            }
        }

        return $sent;
    }
}
