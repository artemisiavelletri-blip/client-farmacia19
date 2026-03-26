<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title></title>

    <!--[if !mso]><!-->
    <style type="text/css">
        @import url('https://fonts.mailersend.com/css?family=Inter:400,600');
    </style>
    <!--<![endif]-->

    <style type="text/css" rel="stylesheet" media="all">
        @media only screen and (max-width: 640px) {
            .ms-header {
                display: none !important;
            }
            .ms-content {
                width: 100% !important;
                border-radius: 0;
            }
            .ms-content-body {
                padding: 30px !important;
            }
            .ms-footer {
                width: 100% !important;
            }
            .mobile-wide {
                width: 100% !important;
            }
            .info-lg {
                padding: 30px;
            }
        }
    </style>
    <!--[if mso]>
    <style type="text/css">
    body { font-family: Arial, Helvetica, sans-serif!important  !important; }
    td { font-family: Arial, Helvetica, sans-serif!important  !important; }
    td * { font-family: Arial, Helvetica, sans-serif!important  !important; }
    td p { font-family: Arial, Helvetica, sans-serif!important  !important; }
    td a { font-family: Arial, Helvetica, sans-serif!important  !important; }
    td span { font-family: Arial, Helvetica, sans-serif!important  !important; }
    td div { font-family: Arial, Helvetica, sans-serif!important  !important; }
    td ul li { font-family: Arial, Helvetica, sans-serif!important  !important; }
    td ol li { font-family: Arial, Helvetica, sans-serif!important  !important; }
    td blockquote { font-family: Arial, Helvetica, sans-serif!important  !important; }
    th * { font-family: Arial, Helvetica, sans-serif!important  !important; }
    </style>
    <![endif]-->
</head>
<body style="font-family:'Inter', Helvetica, Arial, sans-serif; width: 100% !important; height: 100%; margin: 0; padding: 0; -webkit-text-size-adjust: none; background-color: #f4f7fa; color: #4a5566;" >

<div class="preheader" style="display:none !important;visibility:hidden;mso-hide:all;font-size:1px;line-height:1px;max-height:0;max-width:0;opacity:0;overflow:hidden;" ></div>

<table class="ms-body" width="100%" cellpadding="0" cellspacing="0" role="presentation" style="border-collapse:collapse;background-color:#f4f7fa;width:100%;margin-top:0;margin-bottom:0;margin-right:0;margin-left:0;padding-top:0;padding-bottom:0;padding-right:0;padding-left:0;" >
    <tr>
        <td align="center" style="word-break:break-word;font-family:'Inter', Helvetica, Arial, sans-serif;font-size:16px;line-height:24px;" >

            <table class="ms-container" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;width:100%;margin-top:0;margin-bottom:0;margin-right:0;margin-left:0;padding-top:0;padding-bottom:0;padding-right:0;padding-left:0;" >
                <tr>
                    <td align="center" style="word-break:break-word;font-family:'Inter', Helvetica, Arial, sans-serif;font-size:16px;line-height:24px;" >

                        <table class="ms-header" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;" >
                            <tr>
                                <td height="40" style="font-size:0px;line-height:0px;word-break:break-word;font-family:'Inter', Helvetica, Arial, sans-serif;" >
                                    &nbsp;
                                </td>
                            </tr>
                        </table>

                    </td>
                </tr>
                <tr>
                    <td align="center" style="word-break:break-word;font-family:'Inter', Helvetica, Arial, sans-serif;font-size:16px;line-height:24px;" >

                        <table class="ms-content" width="640" cellpadding="0" cellspacing="0" role="presentation" style="border-collapse:collapse;width:640px;margin-top:0;margin-bottom:0;margin-right:auto;margin-left:auto;padding-top:0;padding-bottom:0;padding-right:0;padding-left:0;background-color:#FFFFFF;border-radius:6px;box-shadow:0 3px 6px 0 rgba(0,0,0,.05);" >
                            <tr>
                                <td class="ms-content-body" style="word-break:break-word;font-family:'Inter', Helvetica, Arial, sans-serif;font-size:16px;line-height:24px;padding-top:40px;padding-bottom:40px;padding-right:50px;padding-left:50px;" >

                                    <p class="logo" style="margin-right:0;margin-left:0;line-height:28px;font-weight:600;font-size:21px;color:#111111;text-align:center;margin-top:0;margin-bottom:40px;" ><span style="color:#0052e2;font-family:Arial, Helvetica, sans-serif;font-size:30px;vertical-align:bottom;" ><img src="{{ asset('/img/logo/logo.png') }}" width="200px"></span></p>

                                    <h1 style="margin-top:0;color:#111111;font-size:24px;line-height:36px;font-weight:600;margin-bottom:24px;" >Grazie per il tuo ordine!</h1>

                                    <p style="color:#4a5566;margin-top:20px;margin-bottom:20px;margin-right:0;margin-left:0;font-size:16px;line-height:28px;" >Siamo felici di confermare che la tua richiesta è stata ricevuta con successo.<br> Il tuo ordine <a href="{{ asset('/order-detail/' . $order->order_number) }}" target="_blank">#{{$order->order_number}}</a> verrà elaborato e spedito nel più breve tempo possibile.<br>

                                    @if($order->payment_method == 'bank_transfer')
                                        <p style="color:#4a5566;margin-top:20px;margin-bottom:20px;margin-right:0;margin-left:0;font-size:16px;line-height:28px;" >Ricorda che il tuo ordine sarà preso in carico alla ricezione del pagamento.<br><br>

                                        Restiamo in attesa del bonifico bancario di € {{number_format($order->total, 2, ',', ' ')}} su:<br>

                                        BANCA DI CREDITO COOPERATIVO DI ROMA SOCIETA' COOPERATIVA - IBAN: IT28Z0832739520000000002474, indicando come causale "Farmacia19 {{$order->order_number}}"</p>
                                    @endif

                                    Di seguito il riepilogo dell'ordine. Se hai domande, il nostro team è sempre a tua disposizione per aiutarti.</p><br>

                                     <table width="100%" cellpadding="5" cellspacing="0" role="presentation" style="border-collapse: collapse; text-align:left; border: 1px solid #ddd; font-family: Arial, sans-serif;">
                                        <thead>
                                            <tr style="background-color: #f5f5f5;">
                                                <th style="padding: 8px; border-bottom: 1px solid #ddd; width: 90px;"></th>
                                                <th style="padding: 8px; border-bottom: 1px solid #ddd; width: 200px;">Descrizione</th>
                                                <th style="padding: 8px; border-bottom: 1px solid #ddd; width: 80px; text-align: center;">Quantità</th>
                                                <th style="padding: 8px; border-bottom: 1px solid #ddd; width: 80px; text-align: right;">Prezzo</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($orderItems as $item)
                                                @php $product = $item->product()->first(); @endphp
                                                <tr style="border-bottom: 1px solid #eee;">
                                                    <td style="padding: 8px;">
                                                        <img src="{{ asset('/storage-admin/' . $product->image) }}" width="80" 
                                                             style="border-radius: 5px; display: block;">
                                                    </td>
                                                    <td style="padding: 8px; vertical-align: middle; width: 200px;">
                                                        {{ $item->product_name }}
                                                    </td>
                                                    <td style="padding: 8px; vertical-align: middle; text-align: center; width: 80px;">
                                                        {{ $item->quantity }}
                                                    </td>
                                                    <td style="padding: 8px; vertical-align: middle; text-align: right; width: 80px;">
                                                        € {{ number_format($item->price, 2, ',', '.') }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                            @if($order->payment_method == 'cash_on_delivery')
                                                <tr style="border-bottom: 1px solid #eee;">
                                                    <td style="padding: 8px;">
                                                        <img src="{{ asset('/storage-admin/cash-on-delivery.png') }}" width="50" 
                                                             style="border-radius: 5px; display: block;">
                                                    </td>
                                                    <td style="padding: 8px; vertical-align: middle; width: 200px;">
                                                        Contrassegno
                                                    </td>
                                                    <td style="padding: 8px; vertical-align: middle; text-align: center; width: 80px;">
                                                        
                                                    </td>
                                                    <td style="padding: 8px; vertical-align: middle; text-align: right; width: 80px;">
                                                        € 2,00
                                                    </td>
                                                </tr>
                                            @endif
                                            @if($order->shipping_cost > 0.00)
                                                <tr style="border-bottom: 1px solid #eee;">
                                                    <td style="padding: 8px;">
                                                        <img src="{{ asset('/storage-admin/fast-delivery.png') }}" width="50" 
                                                             style="border-radius: 5px; display: block;">
                                                    </td>
                                                    <td style="padding: 8px; vertical-align: middle; width: 200px;">
                                                        Spedizione
                                                    </td>
                                                    <td style="padding: 8px; vertical-align: middle; text-align: center; width: 80px;">
                                                        
                                                    </td>
                                                    <td style="padding: 8px; vertical-align: middle; text-align: right; width: 80px;">
                                                        € {{ number_format($order->shipping_cost, 2, ',', '.') }}
                                                    </td>
                                                </tr>
                                            @endif
                                            <tr style="border-bottom: 1px solid #eee;background-color: #f5f5f5;">
                                                <td style="padding: 8px;">
                                                </td>
                                                <td style="padding: 8px; vertical-align: middle; width: 200px;">
                                                    
                                                </td>
                                                <td style="padding: 8px; vertical-align: middle; text-align: center; width: 80px;">
                                                    <b>Totale</b>
                                                </td>
                                                <td style="padding: 8px; vertical-align: middle; text-align: right; width: 80px;">
                                                    <b>€ {{ number_format($order->total, 2, ',', '.') }}</b>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>

                                </td>
                            </tr>
                        </table>

                    </td>
                </tr>
                <tr>
                    <td align="center" style="word-break:break-word;font-family:'Inter', Helvetica, Arial, sans-serif;font-size:16px;line-height:24px;" >

                        <table class="ms-footer" width="640" cellpadding="0" cellspacing="0" role="presentation" style="border-collapse:collapse;width:640px;margin-top:0;margin-bottom:0;margin-right:auto;margin-left:auto;" >
                            <tr>
                                <td class="ms-content-body" align="center" style="word-break:break-word;font-family:'Inter', Helvetica, Arial, sans-serif;font-size:16px;line-height:24px;padding-top:40px;padding-bottom:40px;padding-right:50px;padding-left:50px;" >
                                    <p class="small" style="margin-top:20px;margin-bottom:20px;margin-right:0;margin-left:0;color:#96a2b3;font-size:14px;line-height:21px;" > &copy; Copyright <span id="date">{{\Carbon\Carbon::now()->format('Y')}}</span> <a href="{{ asset('/') }}" target="_blank"> Farmacia19 </a> tutti i diritti sono riservati.</p>
                                    </p>
                                </td>
                            </tr>
                        </table>

                    </td>
                </tr>
            </table>

        </td>
    </tr>
</table>

</body>
</html>