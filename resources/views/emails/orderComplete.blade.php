<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ordine consegnato - Farmacia19.it</title>
</head>

<body style="font-family:Inter,Arial,sans-serif;
    margin:0;padding:0;background-color:#f4f7fa;color:#4a5566;">

<div style="display:none;max-height:0;overflow:hidden;opacity:0;">
    Il tuo ordine #{{ $order->order_number }} risulta consegnato.
</div>

<table width="100%" cellpadding="0" cellspacing="0"
       role="presentation" style="background-color:#f4f7fa;">
    <tr>
        <td align="center" style="padding:40px 15px;">

            <table width="100%" cellpadding="0" cellspacing="0"
                   role="presentation"
                   style="max-width:640px;background:#ffffff;
                   border-radius:8px;">

                <tr>
                    <td style="padding:40px 35px;text-align:center;">

                        <!-- Logo Farmacia19 -->
                        <img src="{{ asset('/img/logo/logo.png') }}"
                             alt="Farmacia19.it"
                             width="200"
                             style="max-width:100%;height:auto;">

                        <!-- Titolo -->
                        <h1 style="color:#111111;font-size:25px;
                            line-height:34px;margin:35px 0 20px;">
                            Il tuo ordine è stato consegnato!
                        </h1>

                        <!-- Messaggio -->
                        <p style="font-size:16px;line-height:28px;
                            text-align:left;margin-bottom:20px;">
                            Ciao{{ $order->user?->name ? ' ' . $order->user->name : '' }},
                        </p>

                        <p style="font-size:16px;line-height:28px;
                            text-align:left;">
                            Siamo felici di informarti che il tuo ordine
                            <strong>#{{ $order->order_number }}</strong>
                            risulta consegnato!
                        </p>

                        <p style="font-size:16px;line-height:28px;
                            text-align:left;">
                            Speriamo che la tua esperienza di acquisto
                            su <strong>Farmacia19.it</strong>
                            sia stata piacevole e che tutto sia
                            arrivato secondo le tue aspettative.
                        </p>

                        <!-- Riepilogo consegna -->
                        <table width="100%" cellpadding="0"
                               cellspacing="0" role="presentation"
                               style="margin:30px 0;background:#f4f7fa;
                               border-radius:6px;">
                            <tr>
                                <td style="padding:20px;text-align:left;">
                                    <p style="margin:0 0 10px;
                                        font-size:14px;color:#718096;">
                                        RIFERIMENTO ORDINE
                                    </p>
                                    <p style="margin:0;font-size:19px;
                                        font-weight:600;color:#111111;">
                                        #{{ $order->order_number }}
                                    </p>

                                    <p style="margin:15px 0 0;
                                        color:#23844b;font-weight:600;">
                                        ✓ Spedizione consegnata
                                    </p>
                                </td>
                            </tr>
                        </table>

                        <p style="font-size:16px;line-height:28px;
                            text-align:left;">
                            La tua soddisfazione è importante per noi.
                            Ogni esperienza ci aiuta a migliorare
                            continuamente il nostro servizio.
                        </p>

                        <p style="font-size:16px;line-height:28px;
                            text-align:left;">
                            Per qualsiasi domanda o necessità relativa
                            al tuo ordine, il nostro team è sempre
                            a tua disposizione.
                        </p>

                        <p style="font-size:16px;line-height:28px;
                            text-align:left;margin-top:30px;">
                            Grazie ancora per la fiducia! 💚
                        </p>

                        <p style="font-size:16px;line-height:26px;
                            text-align:left;">
                            <strong>Il team di Farmacia19.it</strong>
                        </p>

                        <!-- Pulsante -->
                        <table cellpadding="0" cellspacing="0"
                               role="presentation"
                               style="margin:30px auto;">
                            <tr>
                                <td bgcolor="#23844b"
                                    style="border-radius:6px;">
                                    <a href="{{ url('/') }}"
                                       style="display:inline-block;
                                       padding:14px 28px;color:#ffffff;
                                       text-decoration:none;
                                       font-size:15px;font-weight:600;">
                                        Torna su Farmacia19.it
                                    </a>
                                </td>
                            </tr>
                        </table>

                    </td>
                </tr>
            </table>

            <!-- Footer -->
            <table width="100%" cellpadding="0" cellspacing="0"
                   role="presentation" style="max-width:640px;">
                <tr>
                    <td align="center"
                        style="padding:30px 20px;
                        color:#96a2b3;font-size:13px;line-height:22px;">

                        &copy; {{ now()->year }}
                        <a href="{{ url('/') }}"
                           style="color:#718096;text-decoration:none;">
                            Farmacia19.it
                        </a>
                        - Tutti i diritti riservati.

                        <p>
                            Questa comunicazione riguarda
                            la consegna del tuo ordine.
                        </p>

                    </td>
                </tr>
            </table>

        </td>
    </tr>
</table>

</body>
</html>
