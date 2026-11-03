@extends('master')


<?php

    // Nexi OneClick - Primo pagamento (PP)
    // Pagamento ordine + registrazione contratto

    $ALIAS = env('XPAY_ALIAS');
    $CHIAVESEGRETA = env('XPAY_SECRET');

    $requestUrl = env('NEXI_BASE_URL');

    // Importo dell'ordine in centesimi
    $importo = (int) round(auth()->user()->cart_total * 100);

    $codTrans = 'PS' . strtoupper(bin2hex(random_bytes(10)));
    $divisa = 'EUR';

    // Contratto per la nuova carta
    $numContratto = "NC_" . date('YmdHis');;

    $tipoRichiesta = 'PP';

    // MAC richiesta Nexi
    $mac = sha1(
        'codTrans=' . $codTrans .
        'divisa=' . $divisa .
        'importo=' . $importo .
        $CHIAVESEGRETA
    );

    // Memorizza il tentativo per la callback
    \Illuminate\Support\Facades\Cache::put(
        'nexi_payment_' . $codTrans,
        [
            'user_id' => auth()->id(),
            'importo' => $importo,
            'divisa' => $divisa,
            'tipo_richiesta' => 'PP',
            'num_contratto' => $numContratto,
        ],
        now()->addHours(2)
    );

    $obbligatori = [
        'alias' => $ALIAS,
        'importo' => $importo,
        'divisa' => $divisa,
        'codTrans' => $codTrans,

        // Ritorno alla funzione pagamento()
        'url' => route('cart.pagamento'),

        'url_back' => route('cart.shop_checkout'),

        'mac' => $mac,
        'num_contratto' => $numContratto,
        'tipo_servizio' => 'paga_oc3d',
        'tipo_richiesta' => $tipoRichiesta,

        // Dati cliente
        'nome' => auth()->user()->name,
        'cognome' => auth()->user()->surname,
        'email' => auth()->user()->email,
    ];

    $facoltativi = [];

    $requestParams = array_merge(
        $obbligatori,
        $facoltativi
    );

?>

<form method="POST" id="addCardForm" action="{{ $requestUrl }}">
    @foreach ($requestParams as $name => $value)
        <input
            type="hidden"
            name="{{ $name }}"
            value="{{ $value }}"
        >
    @endforeach
</form>

<form method="POST" id="gpayForm" action="{{ $requestUrl }}">
    @foreach ($requestParams as $name => $value)
        <input
            type="hidden"
            name="{{ $name }}"
            value="{{ $value }}"
        >
    @endforeach
    <input
        type="hidden"
        name="selectedcard"
        value="GOOGLEPAY"
    >
</form>

<form method="POST" id="applePayForm" action="{{ $requestUrl }}">
    @foreach ($requestParams as $name => $value)
        <input
            type="hidden"
            name="{{ $name }}"
            value="{{ $value }}"
        >
    @endforeach
    <input
        type="hidden"
        name="selectedcard"
        value="APPLEPAY"
    >
</form>


@section('css')

    <style type="text/css">
         /* Modern Vertical List */
        .vertical-list {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .vertical-list-item {
            background: #f8fafc;
            border-radius: var(--border-radius);
            padding: 16px;
            display: flex;
            align-items: center;
            transition: var(--transition);
        }
        
        .vertical-list-item:hover {
            background: #f1f5f9;
        }
        
        .vertical-list-item input[type="radio"] {
            width: 22px;
            height: 22px;
            border: 2px solid var(--gray);
            border-radius: 50%;
            margin-right: 16px;
            position: relative;
            cursor: pointer;
            transition: var(--transition);
        }
        
        .vertical-list-item input[type="radio"]:checked {
            border-color: var(--primary);
            background-color: var(--primary-light);
        }
        
        .vertical-list-item input[type="radio"]:checked::after {
            content: "";
            position: absolute;
            width: 10px;
            height: 10px;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            border-radius: 50%;
            background-color: var(--primary);
        }

        .vertical-list-item label {
            font-weight: 500;
            cursor: pointer;
            flex: 1;
            transition: var(--transition);
        }
        
        .vertical-list-item input[type="radio"]:checked + label {
            color: var(--theme-color);
        }
        
        .vertical-list-item input[type="radio"]:checked ~ .vertical-list-item {
            background: white;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }

        .section-radio {
            background: white;
            padding: 2rem;
            border-radius: var(--border-radius);
            margin-bottom: 2rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.04);
            border: 1px solid rgba(0,0,0,0.05);
            transition: var(--transition);
        }
        
        .section-radio:hover {
            box-shadow: 0 8px 25px rgba(0,0,0,0.06);
            transform: translateY(-2px);
        }
        .card-brand {
            width: 60px;
            height: 40px;
            margin-right: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .card-brand img {
            max-width: 55px;
            max-height: 35px;
            object-fit: contain;
        }

        .card-brand span {
            font-size: 12px;
            font-weight: 700;
        }

        /* Farmacia19 - restyling esclusivo della scelta pagamento */
        #checkoutStep3 .accordion-body { padding: 22px 20px; }
        #checkoutStep3 .payment { display:flex; flex-direction:column; gap:14px; margin-bottom:22px!important; }
        #checkoutStep3 .payment > li[data-id] { width:100%!important; margin:0!important; }
        #checkoutStep3 .payment > li[data-id] > .nav-link {
            display:flex!important; align-items:center!important; gap:18px;
            width:100%!important; min-height:90px; padding:18px 22px!important;
            border:1px solid #e1e7ea!important; border-radius:15px!important;
            background:#fff!important; box-shadow:0 2px 6px rgba(2,51,80,.025);
            color:#233449!important; font-size:16px; font-weight:600;
            transition:border-color .2s, background .2s, box-shadow .2s, transform .2s;
        }
        #checkoutStep3 .payment > li[data-id] > .nav-link:hover {
            border-color:#86cfc9!important; background:#f8fdfc!important;
            box-shadow:0 5px 16px rgba(2,51,80,.06);
        }
        #checkoutStep3 .payment > li[data-id] > .nav-link.active {
            border-color:#03a297!important; background:linear-gradient(105deg,#f5fcfb,#e4f7f5)!important;
            box-shadow:0 0 0 1px rgba(3,162,151,.13);
        }
        #checkoutStep3 .payment > li[data-id] > .nav-link::before { display:none!important; }
        /*#checkoutStep3 .payment > li[data-id] > .nav-link::after {
            content:''; display:block; order:-1; flex:0 0 19px; width:19px; height:19px;
            border:2px solid #b7c1c9; border-radius:50%; background:#fff;
        }*/
        #checkoutStep3 .payment > li[data-id] > .nav-link.active::after {
            border-color:#03a297; background:#03a297;
            box-shadow:inset 0 0 0 4px #fff;
        }
        #checkoutStep3 .payment > li[data-id] .checkout-card-img,
        #checkoutStep3 .payment > li[data-id] .checkout-payment-img {
            display:flex!important; flex:0 0 78px; width:78px; height:50px;
            align-items:center; justify-content:center; margin:0!important;
            border-right:1px solid #e8edef; padding-right:13px;
        }
        #checkoutStep3 .payment > li[data-id] .checkout-card-img img,
        #checkoutStep3 .payment > li[data-id] .checkout-payment-img img {
            max-width:64px!important; max-height:48px!important; width:auto!important;
            height:auto!important; object-fit:contain;
        }
        #checkoutStep3 .payment > li[data-id] .checkout-payment-img.cod img { padding:5px!important; }
        #checkoutStep3 .payment > li[data-id] .nav-link > span { color:#233449!important; }
        #checkoutStep3 .wallet-nav-item { width:100%!important; margin:2px 0 4px!important; padding:0!important; }
        #checkoutStep3 .wallet-buttons { display:grid!important; grid-template-columns:repeat(2,minmax(0,1fr)); gap:14px; width:100%; margin:0; }
        #checkoutStep3 .wallet-btn {
            appearance:none; -webkit-appearance:none; display:flex!important; align-items:center;
            justify-content:center; gap:7px; width:100%!important; height:60px!important;
            padding:10px 16px!important; border-radius:13px!important;
            font-size:25px!important; font-weight:600!important; line-height:1!important;
            cursor:pointer; transition:transform .2s, box-shadow .2s, background .2s;
        }
        #checkoutStep3 .apple-pay-btn { background:#050505!important; color:#fff!important; border:1px solid #050505!important; }
        #checkoutStep3 .google-pay-btn { background:#fff!important; color:#3c4043!important; border:1px solid #cbd3d9!important; }
        #checkoutStep3 .wallet-btn:hover { transform:translateY(-2px); box-shadow:0 6px 16px rgba(0,0,0,.10); }
        #checkoutStep3 .wallet-btn:focus-visible { outline:3px solid #03a297; outline-offset:3px; }
        #checkoutStep3 .wallet-btn img { display:block; width:auto!important; height:70px!important; max-width:140px!important; object-fit:contain; }
        #checkoutStep3 .apple-pay-btn img { filter:brightness(0) invert(1); }
        .payment .wallet-nav-item:has(.wallet-btn[style*="display: none"] + .wallet-btn[style*="display: none"]) {
            display: none;
        }
        @media(max-width:576px) {
            #checkoutStep3 .accordion-body { padding:16px 12px; }
            #checkoutStep3 .payment { gap:11px; }
            #checkoutStep3 .payment > li[data-id] > .nav-link { min-height:78px; padding:14px 12px!important; gap:11px; font-size:14px; }
            #checkoutStep3 .payment > li[data-id] .checkout-card-img,
            #checkoutStep3 .payment > li[data-id] .checkout-payment-img { flex-basis:64px; width:64px; padding-right:8px; }
            #checkoutStep3 .wallet-buttons { gap:10px; }
            #checkoutStep3 .wallet-btn { height:54px!important; }
            #checkoutStep3 .wallet-btn img { height:23px!important; }
        }
        @media (max-width: 767px) {

            #checkoutStep3 .wallet-buttons { grid-template-columns:repeat(1,minmax(0,1fr)); }
        }
        #paypalButtons {
            position: absolute;
            top: 22px;
            opacity: 0;
            width: 100%;
            left: 0;
        }
        /* =========================================
           FARMACIA19 - SOLO APPLE PAY E GOOGLE PAY
           ========================================= */

        #checkoutStep3 .payment > li.f19-wallet-item {
            width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        /* Riquadro wallet */

        #checkoutStep3 .payment > li.f19-wallet-item > .nav-link {
            display: flex !important;
            align-items: center !important;
            gap: 18px !important;

            width: 100% !important;
            min-height: 90px !important;
            padding: 18px 22px !important;

            border: 1px solid #e1e7ea !important;
            border-radius: 15px !important;
            background: #fff !important;

            box-shadow: 0 2px 6px rgba(2, 51, 80, 0.025);

            color: #233449 !important;
            font-size: 16px !important;
            font-weight: 600 !important;

            text-decoration: none !important;
            cursor: pointer;
        }

        /* Hover */

        #checkoutStep3 .payment > li.f19-wallet-item > .nav-link:hover {
            border-color: #86cfc9 !important;
            background: #f8fdfc !important;
        }

        /* Elimina eventuali pseudo-elementi del tema */

        #checkoutStep3 .payment > li.f19-wallet-item > .nav-link::before,
        #checkoutStep3 .payment > li.f19-wallet-item > .nav-link::after {
            display: none !important;
        }

        /* Contenitore logo */

        #checkoutStep3 .payment > li.f19-wallet-item .checkout-card-img {
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;

            flex: 0 0 78px !important;
            width: 78px !important;
            height: 50px !important;

            margin: 0 !important;
            padding-right: 13px !important;

            border-right: 1px solid #e8edef !important;
        }

        /* Logo */

        #checkoutStep3 .payment > li.f19-wallet-item .checkout-card-img img {
            display: block !important;

            max-width: 64px !important;
            max-height: 48px !important;

            width: auto !important;
            height: auto !important;

            object-fit: contain !important;
        }

        /* Testo */

        #checkoutStep3 .payment > li.f19-wallet-item .nav-link > span {
            color: #233449 !important;
            font-size: inherit !important;
            font-weight: 600 !important;
        }

        /* =========================================
           MOBILE
           ========================================= */

        @media (max-width: 576px) {

            #checkoutStep3 .payment > li.f19-wallet-item > .nav-link {
                min-height: 78px !important;
                padding: 14px 12px !important;
                gap: 11px !important;
                font-size: 14px !important;
            }

            #checkoutStep3 .payment > li.f19-wallet-item .checkout-card-img {
                flex: 0 0 64px !important;
                width: 64px !important;
                padding-right: 8px !important;
            }
        }

        /* ========================================
           FARMACIA19 - INDIRIZZI SPEDIZIONE
        ======================================== */

        #checkoutStep2 .f19-shipping-section {
            width: 100%;
            padding: 20px !important;
            margin: 0 !important;
            background: #fff !important;
            border: none !important;
            box-shadow: none !important;
        }

        #checkoutStep2 .f19-address-list {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        /* CARD */

        #checkoutStep2 .f19-address-card {
            position: relative;
            display: flex;
            align-items: center;
            gap: 20px;

            padding: 24px;

            background: #fff;
            border: 1px solid #e1e7ea;
            border-radius: 16px;

            box-shadow: 0 3px 12px rgba(0,0,0,.025);

            transition: all .2s ease;
        }

        #checkoutStep2 .f19-address-card.is-selected {
            background: #f2fbfa;
            border-color: #00a399;
            box-shadow: 0 0 0 1px rgba(0,163,153,.08);
        }

        #checkoutStep2 .f19-address-card:hover {
            border-color: #00a399;
        }

        /* RADIO NASCOSTO */

        #checkoutStep2 .f19-address-radio {
            position: absolute !important;
            width: 1px !important;
            height: 1px !important;
            padding: 0 !important;
            margin: -1px !important;
            opacity: 0 !important;
            overflow: hidden !important;
        }

        /* LABEL */

        #checkoutStep2 .f19-address-details {
            display: flex;
            align-items: center;
            gap: 22px;

            flex: 1;
            min-width: 0;
            margin: 0;

            cursor: pointer;
        }

        /* ICONA GRANDE */

        #checkoutStep2 .f19-address-icon {
            display: flex;
            align-items: center;
            justify-content: center;

            flex: 0 0 76px;
            width: 76px;
            height: 76px;

            border-right: 1px solid #dce8e8;

            color: #009f96;
            font-size: 27px;
        }

        /* CONTENUTO */

        #checkoutStep2 .f19-address-content {
            display: flex;
            flex-direction: column;
            gap: 10px;

            flex: 1;
            min-width: 0;
        }

        #checkoutStep2 .f19-address-name {
            color: #132c47;
            font-size: 18px;
            font-weight: 700;
        }

        #checkoutStep2 .f19-address-line {
            display: flex;
            align-items: flex-start;
            gap: 12px;

            color: #263b50;
            font-size: 15px;
            line-height: 1.5;
        }

        #checkoutStep2 .f19-address-line i {
            flex: 0 0 18px;
            margin-top: 4px;

            color: #009f96;
            font-size: 16px;
        }

        /* NOTE */

        #checkoutStep2 .f19-address-note {
            display: inline-flex;
            align-items: center;
            gap: 10px;

            align-self: flex-start;

            padding: 8px 12px;

            background: #e9f0f1;
            border-radius: 9px;

            color: #263b50;
            font-size: 14px;
        }

        #checkoutStep2 .f19-address-note i {
            color: #009f96;
        }

        /* MODIFICA */

        #checkoutStep2 .f19-edit-address {
            flex-shrink: 0;

            padding: 11px 18px;

            background: #fff;
            border: 1px solid #00a399;
            border-radius: 10px;

            color: #008e86;
            font-size: 14px;
            font-weight: 600;

            cursor: pointer;
        }

        #checkoutStep2 .f19-edit-address:hover {
            background: #e8f8f6;
        }

        #checkoutStep2 .f19-edit-address.hidden {
            display: none !important;
        }

        /* AGGIUNGI INDIRIZZO */

        #checkoutStep2 .f19-add-address-wrapper {
            margin-top: 20px;
        }

        #checkoutStep2 .f19-add-address {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;

            width: 100%;
            min-height: 54px;

            border-radius: 12px;

            font-size: 15px;
            font-weight: 600;
        }

        /* MOBILE */

        @media (max-width: 767px) {

            #checkoutStep2 .f19-shipping-section {
                padding: 10px !important;
            }

            #checkoutStep2 .f19-address-card {
                flex-wrap: wrap;
                padding: 16px 12px;
                gap: 12px;
            }

            #checkoutStep2 .f19-address-details {
                align-items: flex-start;
                gap: 12px;
            }

            #checkoutStep2 .f19-address-icon {
                flex: 0 0 40px;
                width: 40px;
                height: 48px;
                font-size: 22px;
            }

            #checkoutStep2 .f19-address-name {
                font-size: 16px;
            }

            #checkoutStep2 .f19-address-line {
                font-size: 13px;
                gap: 8px;
            }

            #checkoutStep2 .f19-address-note {
                font-size: 12px;
            }

            #checkoutStep2 .f19-edit-address {
                width: 100%;
                margin-left: 0;
                text-align: center;
            }

            #checkoutStep2 .f19-add-address-wrapper {
                margin-top: 14px;
            }

            #checkoutStep2 .f19-add-address {
                min-height: 48px;
                font-size: 14px;
            }
        }

        /* FARMACIA19 - CORREZIONE AGGIUNGI INDIRIZZO */

        #checkoutStep2 .f19-add-address {
            background: #009f96 !important;
            background-image: none !important;
            color: #ffffff !important;
            border: 1px solid #009f96 !important;
            opacity: 1 !important;
        }

        #checkoutStep2 .f19-add-address i,
        #checkoutStep2 .f19-add-address span {
            color: #ffffff !important;
        }

        /* Disattiva eventuali sovrapposizioni del tema */
        #checkoutStep2 .f19-add-address::before,
        #checkoutStep2 .f19-add-address::after {
            display: none !important;
        }

        /* Hover */
        #checkoutStep2 .f19-add-address:hover {
            background: #00877f !important;
            border-color: #00877f !important;
            color: #ffffff !important;
        }

        /* Mobile */
        @media (max-width: 767px) {
            #checkoutStep2 .f19-add-address {
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                gap: 10px !important;

                width: 100% !important;
                min-height: 50px !important;

                background: #009f96 !important;
                color: #ffffff !important;

                border-radius: 12px !important;
                font-size: 14px !important;
                font-weight: 600 !important;
            }
        }
        /* ==========================================
           FARMACIA19 - CARTE SALVATE
           VERSIONE DEFINITIVA
        ========================================== */

        /* CONTENITORE */

        #pills-1 .f19-cards-section {
            width: 100%;
            padding: 20px !important;
            margin: 0 !important;
            background: #ffffff !important;
            border: none !important;
            box-shadow: none !important;
        }

        /* LISTA CARTE */

        #pills-1 .f19-cards-list {
            display: flex !important;
            flex-direction: column !important;
            gap: 10px !important;

            width: 100%;
            margin: 0 !important;
            padding: 0 !important;
        }

        /* ==========================================
           SINGOLA CARTA
        ========================================== */

        #pills-1 .f19-saved-card {
            position: relative !important;

            display: block !important;
            width: 100% !important;

            padding: 0 !important;
            margin: 0 !important;

            background: #ffffff !important;
            border: 1px solid #dce5e9 !important;
            border-radius: 14px !important;

            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.025);

            overflow: hidden;

            transition:
                background-color 0.2s ease,
                border-color 0.2s ease,
                box-shadow 0.2s ease;
        }

        /* CARTA SELEZIONATA */

        #pills-1 .f19-saved-card.is-selected,
        #pills-1 .f19-saved-card:has(
            input[name="selected_card_id"]:checked
        ) {
            background: #eaf9f7 !important;
            border-color: #00a399 !important;
            box-shadow: 0 0 0 1px rgba(0, 163, 153, 0.06) !important;
        }

        /* HOVER */

        #pills-1 .f19-saved-card:hover {
            border-color: #00a399 !important;
        }

        /* ==========================================
           RADIO BUTTON NASCOSTO
        ========================================== */

        #pills-1 .f19-card-radio {
            position: absolute !important;

            width: 1px !important;
            height: 1px !important;

            margin: -1px !important;
            padding: 0 !important;

            opacity: 0 !important;
            overflow: hidden !important;

            border: 0 !important;
        }

        /* ACCESSIBILITÀ TASTIERA */

        #pills-1 .f19-saved-card:focus-within {
            outline: 2px solid #00a399;
            outline-offset: 2px;
        }

        /* ==========================================
           LABEL CLICCABILE
        ========================================== */

        #pills-1 .f19-card-label {
            display: flex !important;
            align-items: center !important;

            gap: 16px !important;

            width: 100% !important;
            min-height: 88px !important;

            padding: 16px 18px !important;
            margin: 0 !important;

            background: transparent !important;

            cursor: pointer !important;
        }

        /* ==========================================
           LOGO CARTA
        ========================================== */

        #pills-1 .f19-card-brand {
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;

            flex: 0 0 82px !important;

            width: 82px !important;
            height: 58px !important;

            padding-right: 16px !important;
            margin: 0 !important;

            border-right: 1px solid #dce8e8 !important;
        }

        /* IMMAGINI VISA / MASTERCARD */

        #pills-1 .f19-card-brand img {
            display: block !important;

            width: auto !important;
            height: auto !important;

            max-width: 65px !important;
            max-height: 42px !important;

            object-fit: contain !important;
        }

        /* ALTRE CARTE */

        #pills-1 .f19-brand-text {
            color: #16334b !important;
            font-size: 13px !important;
            font-weight: 700 !important;
        }

        /* ==========================================
           INFORMAZIONI CARTA
        ========================================== */

        #pills-1 .f19-card-information {
            display: flex !important;
            flex-direction: column !important;

            align-items: flex-start !important;
            justify-content: center !important;

            flex: 1 1 auto !important;
            min-width: 0 !important;

            gap: 5px !important;

            margin: 0 !important;
            padding: 0 !important;

            text-align: left !important;
        }

        /* BRAND */

        #pills-1 .f19-card-name {
            display: block !important;

            color: #132c47 !important;
            font-size: 16px !important;
            font-weight: 700 !important;

            line-height: 1.3 !important;
        }

        /* TITOLARE */

        #pills-1 .f19-card-holder {
            display: block !important;

            color: #263b50 !important;
            font-size: 14px !important;
            font-weight: 500 !important;

            line-height: 1.4 !important;

            overflow-wrap: anywhere;
        }

        /* SCADENZA */

        #pills-1 .f19-card-expiry {
            position: static !important;

            display: block !important;

            width: auto !important;

            margin: 0 !important;
            padding: 0 !important;

            color: #64748b !important;
            font-size: 12px !important;
            font-weight: 400 !important;

            line-height: 1.4 !important;
            text-align: left !important;

            transform: none !important;
        }

        /* ==========================================
           SPUNTA CARTA SELEZIONATA
        ========================================== */

        #pills-1 .f19-card-check {
            display: none !important;

            align-items: center !important;
            justify-content: center !important;

            flex: 0 0 28px !important;

            width: 28px !important;
            height: 28px !important;

            margin-left: auto !important;

            border-radius: 50% !important;

            background: #00a399 !important;
            color: #ffffff !important;

            font-size: 12px !important;
        }

        /* SPUNTA VISIBILE SOLO SULLA CARTA SELEZIONATA */

        #pills-1 .f19-saved-card.is-selected .f19-card-check,
        #pills-1 .f19-saved-card:has(
            input[name="selected_card_id"]:checked
        ) .f19-card-check {
            display: flex !important;
        }

        #pills-1 .f19-card-check i {
            color: #ffffff !important;
        }

        /* ==========================================
           AGGIUNGI CARTA
        ========================================== */

        #pills-1 .f19-add-card-wrapper {
            width: 100%;
            margin-top: 14px !important;
        }

        /* PULSANTE */

        #pills-1 .f19-add-card {
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;

            gap: 10px !important;

            width: 100% !important;
            min-height: 52px !important;

            padding: 12px 18px !important;

            background: #009f96 !important;
            background-image: none !important;

            border: 1px solid #009f96 !important;
            border-radius: 12px !important;

            color: #ffffff !important;

            font-size: 15px !important;
            font-weight: 600 !important;

            line-height: 1.4 !important;

            opacity: 1 !important;
            cursor: pointer !important;

            transition:
                background-color 0.2s ease,
                border-color 0.2s ease;
        }

        /* ICONA E TESTO BIANCHI */

        #pills-1 .f19-add-card span,
        #pills-1 .f19-add-card i {
            color: #ffffff !important;
        }

        /* ELIMINA EFFETTI DEL TEMA */

        #pills-1 .f19-add-card::before,
        #pills-1 .f19-add-card::after {
            display: none !important;
        }

        /* HOVER */

        #pills-1 .f19-add-card:hover {
            background: #00877f !important;
            border-color: #00877f !important;
            color: #ffffff !important;
        }

        /* FOCUS */

        #pills-1 .f19-add-card:focus-visible {
            outline: 2px solid #006f69 !important;
            outline-offset: 3px !important;
        }

        /* ==========================================
           TABLET
        ========================================== */

        @media (max-width: 991px) {

            #pills-1 .f19-cards-section {
                padding: 15px !important;
            }

            #pills-1 .f19-card-label {
                padding: 14px 15px !important;
            }

        }

        /* ==========================================
           MOBILE
        ========================================== */

        @media (max-width: 767px) {

            /* CONTENITORE */

            #pills-1 .f19-cards-section {
                padding: 10px !important;
            }

            /* LISTA */

            #pills-1 .f19-cards-list {
                gap: 10px !important;
            }

            /* CARD */

            #pills-1 .f19-saved-card {
                border-radius: 12px !important;
            }

            /* LABEL */

            #pills-1 .f19-card-label {
                min-height: 84px !important;

                padding: 12px !important;

                gap: 12px !important;
            }

            /* LOGO */

            #pills-1 .f19-card-brand {
                flex: 0 0 60px !important;

                width: 60px !important;
                height: 50px !important;

                padding-right: 10px !important;
            }

            #pills-1 .f19-card-brand img {
                max-width: 48px !important;
                max-height: 34px !important;
            }

            /* BRAND */

            #pills-1 .f19-card-name {
                font-size: 14px !important;
            }

            /* TITOLARE */

            #pills-1 .f19-card-holder {
                font-size: 13px !important;
            }

            /* SCADENZA */

            #pills-1 .f19-card-expiry {
                font-size: 12px !important;
            }

            /* SPUNTA */

            #pills-1 .f19-card-check {
                flex: 0 0 24px !important;

                width: 24px !important;
                height: 24px !important;

                font-size: 10px !important;
            }

            /* PULSANTE AGGIUNGI CARTA */

            #pills-1 .f19-add-card-wrapper {
                margin-top: 12px !important;
            }

            #pills-1 .f19-add-card {
                min-height: 48px !important;

                padding: 11px 14px !important;

                border-radius: 11px !important;

                font-size: 14px !important;

                background: #009f96 !important;
                color: #ffffff !important;
            }

        }

        /* ==========================================
           SMARTPHONE PICCOLI
        ========================================== */

        @media (max-width: 375px) {

            #pills-1 .f19-card-label {
                gap: 9px !important;
                padding: 10px !important;
            }

            #pills-1 .f19-card-brand {
                flex-basis: 52px !important;
                width: 52px !important;
                padding-right: 8px !important;
            }

            #pills-1 .f19-card-brand img {
                max-width: 42px !important;
            }

            #pills-1 .f19-card-check {
                flex-basis: 22px !important;
                width: 22px !important;
                height: 22px !important;
            }

        }
    
        /* FARMACIA19 - INTESTAZIONE CARTE SALVATE (solo #pills-1) */
        #pills-1 .f19-cards-header {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 20px;
            padding-bottom: 18px;
            border-bottom: 1px solid #e6eeee;
        }
        #pills-1 .f19-cards-header-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 46px;
            width: 46px;
            height: 46px;
            background: #e8f8f6;
            border-radius: 12px;
            color: #009f96;
            font-size: 21px;
        }
        #pills-1 .f19-cards-header-text { flex: 1; min-width: 0; }
        #pills-1 .f19-cards-header-text h4 {
            margin: 0 0 4px !important;
            color: #132c47 !important;
            font-size: 18px !important;
            font-weight: 700 !important;
        }
        #pills-1 .f19-cards-header-text p {
            margin: 0 !important;
            color: #64748b !important;
            font-size: 13px !important;
            line-height: 1.5;
        }
        @media (max-width: 767px) {
            #pills-1 .f19-cards-header {
                gap: 11px;
                margin-bottom: 16px;
                padding-bottom: 14px;
            }
            #pills-1 .f19-cards-header-icon {
                flex-basis: 40px; width: 40px; height: 40px; font-size: 18px;
            }
            #pills-1 .f19-cards-header-text h4 { font-size: 16px !important; }
            #pills-1 .f19-cards-header-text p { font-size: 12px !important; }
        }


    /* FARMACIA19 - SOLO INDIRIZZO DI FATTURAZIONE */
    #checkoutStep1 .f19-billing-card {
        background: #f2fbfa; border: 1px solid #13b6ad; border-radius: 15px;
        padding: 24px 28px; color: #142d46; width: 100%;
    }
    #checkoutStep1 .f19-billing-top { display: flex; align-items: center; gap: 18px; margin-bottom: 20px; }
    #checkoutStep1 .f19-billing-symbol { display: flex; align-items: center; justify-content: center; flex: 0 0 62px; height: 62px; border-radius: 14px; background: #def4f1; color: #009f96; font-size: 28px; }
    #checkoutStep1 .f19-billing-identity { flex: 1; min-width: 0; }
    #checkoutStep1 .f19-billing-identity h3 { font-size: 21px; font-weight: 700; color: #142d46; margin: 0; line-height: 1.35; }
    #checkoutStep1 .f19-billing-company { font-size: 14px; margin-bottom: 5px; color: #008f87; font-weight: 600; }
    #checkoutStep1 .f19-billing-edit { display: inline-flex; align-items: center; justify-content: center; gap: 9px; padding: 10px 16px; border: 1px solid #009f96; border-radius: 11px; color: #008f87; background: #fff; font-size: 14px; font-weight: 600; text-decoration: none; white-space: nowrap; }
    #checkoutStep1 .f19-billing-edit:hover { background: #e6f7f4; color: #007d76; }
    #checkoutStep1 .f19-billing-details { display: flex; flex-direction: column; }
    #checkoutStep1 .f19-billing-row { display: flex; align-items: flex-start; gap: 16px; padding: 11px 2px; border-bottom: 1px solid #d6e9e7; font-size: 15px; line-height: 1.5; overflow-wrap: anywhere; }
    #checkoutStep1 .f19-billing-row:last-child { border-bottom: none; }
    #checkoutStep1 .f19-billing-row > i { flex: 0 0 22px; color: #009f96; text-align: center; font-size: 17px; margin-top: 3px; }
    #checkoutStep1 .f19-billing-row strong { color: #142d46; margin-right: 5px; }
    @media (max-width: 767px) {
        #checkoutStep1 .f19-billing-card { padding: 16px 14px; border-radius: 12px; }
        #checkoutStep1 .f19-billing-top { gap: 10px; flex-wrap: wrap; margin-bottom: 12px; }
        #checkoutStep1 .f19-billing-symbol { flex-basis: 42px; height: 42px; font-size: 20px; border-radius: 10px; }
        #checkoutStep1 .f19-billing-identity h3 { font-size: 16px; }
        #checkoutStep1 .f19-billing-edit { margin-left: auto; padding: 8px 11px; font-size: 12px; }
        #checkoutStep1 .f19-billing-row { font-size: 13px; gap: 10px; padding: 10px 0; }
        #checkoutStep1 .f19-billing-row > i { flex-basis: 18px; font-size: 15px; }
    }
    </style>

@endsection

@section('content')

    <main class="main">

        <!-- shop checkout -->
        <div class="shop-checkout py-90">
            <div class="container">
                <div class="shop-checkout-wrap">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    @if (session()->has('error'))
                        <div class="alert alert-danger">
                            {{ session()->get('error') }}
                        </div>
                    @endif
                    <form action="/payment/order" id="payForm" method="POST">
                        @csrf
                        <input type="hidden" name="paypal" id="paypal-order">
                        <div class="row">
                            <div class="col-lg-8">
                                <div class="shop-checkout-step">
                                    <div class="accordion" id="shopCheckout">
                                        <div class="accordion-item">
                                          <h2 class="accordion-header">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#checkoutStep1" aria-expanded="false" aria-controls="checkoutStep1">
                                                Indirizzo di Fatturazione
                                            </button>
                                          </h2>
                                          <div id="checkoutStep1" class="accordion-collapse collapse" data-bs-parent="#shopCheckout">
                                            <div class="accordion-body">
                                                <div class="f19-billing-card">
                                                    <div class="f19-billing-top">
                                                        <div class="f19-billing-symbol" aria-hidden="true"><i class="far fa-file-alt"></i></div>
                                                        <div class="f19-billing-identity">
                                                            @if($user->user_type)
                                                                <div class="f19-billing-company">{{ $user->company_society }}</div>
                                                            @endif
                                                            <h3>{{ $user->name }} {{ $user->surname }}</h3>
                                                        </div>
                                                        <a class="f19-billing-edit" href="/settings/billing-address"><i class="fas fa-edit" aria-hidden="true"></i> Modifica</a>
                                                    </div>
                                                    <div class="f19-billing-details">
                                                        <div class="f19-billing-row"><i class="fas fa-map-marker-alt" aria-hidden="true"></i><div><strong>Indirizzo:</strong> {{ $user->billingAddresses()->first()->address }}</div></div>
                                                        @if($user->billingAddresses()->first()->note)
                                                            <div class="f19-billing-row"><i class="far fa-sticky-note" aria-hidden="true"></i><div><strong>Note:</strong> {{ $user->billingAddresses()->first()->note }}</div></div>
                                                        @endif
                                                        <div class="f19-billing-row"><i class="fas fa-building" aria-hidden="true"></i><div><strong>CAP:</strong> {{ $user->billingAddresses()->first()->cap }}</div></div>
                                                        <div class="f19-billing-row"><i class="fas fa-map-marked-alt" aria-hidden="true"></i><div><strong>Città (Provincia):</strong> {{ $user->billingAddressesCity()->name }} ({{ $user->billingAddressesCity()->province }})</div></div>
                                                        <div class="f19-billing-row"><i class="fas fa-phone-alt" aria-hidden="true"></i><div><strong>Telefono:</strong> {{ $user->billingAddresses()->first()->phone }}</div></div>
                                                        @if(!$user->user_type)
                                                            <div class="f19-billing-row"><i class="far fa-id-card" aria-hidden="true"></i><div><strong>C.F.:</strong> {{ $user->cf }}</div></div>
                                                        @else
                                                            <div class="f19-billing-row"><i class="far fa-id-card" aria-hidden="true"></i><div><strong>C.F.:</strong> {{ $user->company_cf }}</div></div>
                                                            <div class="f19-billing-row"><i class="far fa-file-alt" aria-hidden="true"></i><div><strong>P.Iva:</strong> {{ $user->company_pi }}</div></div>
                                                            <div class="f19-billing-row"><i class="far fa-envelope" aria-hidden="true"></i><div><strong>Pec:</strong> {{ $user->company_pec }}</div></div>
                                                            <div class="f19-billing-row"><i class="fas fa-hashtag" aria-hidden="true"></i><div><strong>Codice SDI:</strong> {{ $user->company_sdi }}</div></div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                          </div>
                                        </div>
                                        <div class="accordion-item">
                                          <h2 class="accordion-header">
                                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#checkoutStep2" aria-expanded="true" aria-controls="checkoutStep2">
                                                Indirizzo di Spedizione
                                            </button>
                                          </h2>
                                          <div id="checkoutStep2"
                                             class="accordion-collapse collapse show"
                                             data-bs-parent="#shopCheckout">

                                            <div class="accordion-body">
                                                <div class="row shop-checkout-form">

                                                    <div class="section-radio f19-shipping-section">

                                                        <div class="vertical-list f19-address-list">

                                                            @foreach(Auth::user()->shippingAddresses as $address)

                                                                <div class="vertical-list-item f19-address-card {{ $address->default ? 'is-selected' : '' }}">

                                                                    <input
                                                                        type="radio"
                                                                        class="f19-address-radio"
                                                                        id="shipping_address_{{ $address->id }}"
                                                                        name="shipping_address"
                                                                        value="{{ $address->id }}"
                                                                        @checked($address->default)
                                                                    >

                                                                    <label for="shipping_address_{{ $address->id }}"
                                                                           class="f19-address-details">

                                                                        <span class="f19-address-icon">
                                                                            <i class="fas fa-map-marker-alt"></i>
                                                                        </span>

                                                                        <span class="f19-address-content">

                                                                            <span class="f19-address-name">
                                                                                {{ $address->recipient_name }}
                                                                            </span>

                                                                            <span class="f19-address-line">
                                                                                <i class="fas fa-map-marker-alt"></i>

                                                                                <span>
                                                                                    {{ $address->address }},
                                                                                    {{ $address->cap }}
                                                                                    {{ $address->city->name }}
                                                                                    ({{ $address->city->province }})
                                                                                </span>
                                                                            </span>

                                                                            <span class="f19-address-line">
                                                                                <i class="fas fa-phone-alt"></i>
                                                                                <span>{{ $address->phone }}</span>
                                                                            </span>

                                                                            @if($address->note)
                                                                                <span class="f19-address-note">
                                                                                    <i class="far fa-sticky-note"></i>
                                                                                    {{ $address->note }}
                                                                                </span>
                                                                            @endif

                                                                        </span>

                                                                    </label>

                                                                    <button
                                                                        type="button"
                                                                        class="edit-address f19-edit-address {{ $address->default ? '' : 'hidden' }}"
                                                                        onclick="window.location.href='/settings/edit-address/shipping/{{ $address->id }}'">

                                                                        <i class="far fa-edit"></i>
                                                                        Modifica indirizzo

                                                                    </button>

                                                                </div>

                                                            @endforeach

                                                        </div>

                                                        <div class="f19-add-address-wrapper">

                                                            <button
                                                                type="button"
                                                                class="theme-btn f19-add-address"
                                                                onclick="window.location.href='/settings/create-address/shipping'">

                                                                <i class="far fa-plus-circle"></i>
                                                                Aggiungi Indirizzo

                                                            </button>

                                                        </div>

                                                    </div>

                                                </div>
                                            </div>
                                        </div>
                                        </div>
                                        <div class="accordion-item">
                                          <h2 class="accordion-header">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#checkoutStep3" aria-expanded="false" aria-controls="checkoutStep3">
                                               Metodo di Pagamento
                                            </button>
                                            <input type="hidden" id="paymentMethod" name="payment_method">
                                          </h2>
                                          <div id="checkoutStep3" class="accordion-collapse collapse" data-bs-parent="#shopCheckout">
                                            <div class="accordion-body">
                                                <div class="shop-checkout-payment">
                                                    <ul class="nav nav-pills mb-3 payment" id="pills-tab" role="tablist">
                                                        <!-- PAGA CON CARTA -->
                                                        <li class="nav-item" role="presentation" data-id="stripe" style="width:100%!important;">
                                                            <a class="nav-link" id="pills-tab-1" data-bs-toggle="pill"
                                                               data-bs-target="#pills-1" type="button" role="tab"
                                                               aria-controls="pills-1" aria-selected="true"
                                                               style="text-align: left!important;">

                                                                <div class="checkout-card-img" style="display: inline;">
                                                                    <img src="{{ asset('/img/payment/cards.png') }}"
                                                                         alt="Carte di pagamento"
                                                                         style="width: 80px!important;">
                                                                </div>

                                                                <span>Paga con Carta</span>
                                                            </a>
                                                        </li>

                                                        {{-- ========================================= --}}
                                                        {{-- APPLE PAY --}}
                                                        {{-- ========================================= --}}

                                                        <li class="nav-item f19-wallet-item"
                                                            role="presentation"
                                                            id="applePayItem"
                                                            style="width:100%!important; display:none;">

                                                            <a class="nav-link"
                                                               id="applePayBtn"
                                                               role="button"
                                                               tabindex="0"
                                                               style="text-align:left!important; cursor:pointer;">

                                                                <div class="checkout-card-img">
                                                                    <img src="{{ asset('/img/payment/apple-pay.png') }}"
                                                                         alt="Apple Pay">
                                                                </div>

                                                                <span>Apple Pay</span>

                                                            </a>
                                                        </li>


                                                        {{-- ========================================= --}}
                                                        {{-- GOOGLE PAY --}}
                                                        {{-- ========================================= --}}

                                                        <li class="nav-item f19-wallet-item"
                                                            role="presentation"
                                                            id="googlePayItem"
                                                            style="width:100%!important;">

                                                            <a class="nav-link"
                                                               id="googlePayBtn"
                                                               role="button"
                                                               tabindex="0"
                                                               style="text-align:left!important; cursor:pointer;">

                                                                <div class="checkout-card-img">
                                                                    <img src="{{ asset('/img/payment/google-pay.svg') }}"
                                                                         alt="Google Pay">
                                                                </div>

                                                                <span>Google Pay</span>

                                                            </a>
                                                        </li>

                                                        <!-- BONIFICO -->
                                                        <li class="nav-item" role="presentation"
                                                            data-id="bank_transfer" style="width:100%!important">

                                                            <a class="nav-link" id="pills-tab-3" data-bs-toggle="pill"
                                                               data-bs-target="#pills-3" type="button" role="tab"
                                                               aria-controls="pills-3" aria-selected="false"
                                                               style="text-align: left!important;">

                                                                <div class="checkout-payment-img" style="display: inline;">
                                                                    <img src="{{ asset('/img/payment/bonifico.png') }}"
                                                                         alt=""
                                                                         style="width: 80px!important;">
                                                                </div>

                                                                <span>Paga con Bonifico</span>
                                                            </a>
                                                        </li>

                                                        <!-- PAGAMENTO ALLA CONSEGNA -->
                                                        <li class="nav-item" role="presentation"
                                                            data-id="cod" style="width:100%!important">

                                                            <a class="nav-link" id="pills-tab-4" data-bs-toggle="pill"
                                                               data-bs-target="#pills-4" type="button" role="tab"
                                                               aria-controls="pills-4" aria-selected="false"
                                                               style="text-align: left!important;">

                                                                <div class="checkout-payment-img cod" style="display: inline;">
                                                                    <img src="{{ asset('/img/payment/cash.png') }}"
                                                                         alt=""
                                                                         style="width: 80px!important;padding: 10px!important;">
                                                                </div>

                                                                <span>Paga alla Consegna</span>
                                                            </a>
                                                        </li>
                                                        <li class="nav-item" role="presentation"
                                                            data-id="cod" style="width:100%!important">

                                                            <a class="nav-link" type="button">
                                                                <div class="checkout-payment-img cod" style="display: inline;">
                                                                    <img src="{{ asset('/img/payment/paypal-3.png') }}"
                                                                         alt=""
                                                                         style="width: 80px!important;padding: 10px!important;">
                                                                </div>

                                                                <span>PayPal</span>
                                                                <div class="mb-15" id="paypalButtons" style="position:absolute;top: 0;opacity: 0;">
                                                                </div>
                                                            </a>
                                                        </li>
                                                    </ul>
                                                    
                                                    <div class="tab-content" id="pills-tabContent">
                                                        <div class="tab-pane fade show"
                                                         id="pills-1"
                                                         role="tabpanel"
                                                         aria-labelledby="pills-tab-1"
                                                         tabindex="0">

                                                        <div class="shop-checkout-form">

                                                            <div class="section-radio f19-cards-section">

                                                                {{-- Intestazione carte salvate --}}
                                                                <div class="f19-cards-header">
                                                                    <div class="f19-cards-header-icon" aria-hidden="true">
                                                                        <i class="far fa-credit-card"></i>
                                                                    </div>
                                                                    <div class="f19-cards-header-text">
                                                                        <h4>Carte Salvate</h4>
                                                                        <p>Seleziona una carta per completare il pagamento</p>
                                                                    </div>
                                                                </div>

                                                                <div class="vertical-list f19-cards-list">

                                                                    @foreach($user->paymentMethods()->get() as $card)

                                                                        @php
                                                                            $brand = strtolower($card->brand);
                                                                        @endphp

                                                                        <div class="vertical-list-item f19-saved-card {{ $card->default ? 'is-selected' : '' }}">

                                                                            {{-- Radio nascosto, ma funzionante --}}
                                                                            <input
                                                                                type="radio"
                                                                                class="f19-card-radio"
                                                                                id="saved_card_{{ $card->id }}"
                                                                                name="selected_card_id"
                                                                                value="{{ $card->id }}"
                                                                                @checked($card->default)
                                                                            >

                                                                            {{-- Card cliccabile --}}
                                                                            <label
                                                                                for="saved_card_{{ $card->id }}"
                                                                                class="f19-card-label">

                                                                                <span class="f19-card-brand">

                                                                                    @if($brand === 'visa')

                                                                                        <img
                                                                                            src="{{ asset('/img/payment/visa.svg') }}"
                                                                                            alt="Visa">

                                                                                    @elseif($brand === 'mastercard')

                                                                                        <img
                                                                                            src="{{ asset('/img/payment/mastercard.png') }}"
                                                                                            alt="Mastercard">

                                                                                    @else

                                                                                        <span class="f19-brand-text">
                                                                                            {{ strtoupper($card->brand) }}
                                                                                        </span>

                                                                                    @endif

                                                                                </span>

                                                                                <span class="f19-card-information">

                                                                                    <span class="f19-card-name">
                                                                                        {{ strtoupper($card->brand) }}
                                                                                    </span>

                                                                                    <span class="f19-card-holder">
                                                                                        {{ $card->holder_name }}
                                                                                    </span>

                                                                                    <span class="f19-card-expiry">
                                                                                        Scadenza:
                                                                                        {{ str_pad($card->exp_month, 2, '0', STR_PAD_LEFT) }}/{{ $card->exp_year }}
                                                                                    </span>

                                                                                </span>

                                                                                {{-- Indicatore carta selezionata --}}
                                                                                <span class="f19-card-check" aria-hidden="true">
                                                                                    <i class="fas fa-check"></i>
                                                                                </span>

                                                                            </label>

                                                                        </div>

                                                                    @endforeach

                                                                </div>

                                                                {{-- Aggiungi carta: mantiene la classe addCard --}}
                                                                <div class="f19-add-card-wrapper">

                                                                    <button
                                                                        class="theme-btn addCard f19-add-card w-100 btn-sm"
                                                                        type="button">

                                                                        <span class="far fa-plus-circle"></span>
                                                                        Aggiungi Carta

                                                                    </button>

                                                                </div>

                                                            </div>

                                                        </div>

                                                    </div>
                                                        <div class="tab-pane fade" id="pills-2" role="tabpanel"
                                                            aria-labelledby="pills-tab-2" tabindex="0">
                                                            <div class="shop-checkout-form">
                                                            </div>
                                                        </div>
                                                        <div class="tab-pane fade" id="pills-3" role="tabpanel"
                                                            aria-labelledby="pills-tab-3" tabindex="0">
                                                            <div class="shop-checkout-form">
                                                                <p><b>NB:</b> questo metodo di pagamento richiede 24h in più per la spedizione.</p>
                                                            </div>
                                                        </div>
                                                        <div class="tab-pane fade" id="pills-4" role="tabpanel"
                                                            aria-labelledby="pills-tab-4" tabindex="0">
                                                            <div class="shop-checkout-form cod">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                          </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div id="shop-cart-summary" class="shop-cart-summary">
                                    <h5>Riepilogo Carrello</h5>
                                    <ul>
                                        <li><strong>Sub Totale:</strong> <span>€{{number_format((float)auth()->user()->cartItems()->with('product')->get()->sum->subtotalnoiva, 2, '.', '')}}</span></li>
                                        <!-- <li><strong>Discount:</strong> <span>$5.00</span></li> -->
                                        <li><strong>Spese di Spedizione:</strong> <span>
                                            @if(auth()->user()->cart_shipping_cost > 0)
                                                €{{ number_format(auth()->user()->cart_shipping_cost, 2, ',', '.') }}
                                            @else
                                                Gratuite
                                            @endif
                                        </span></li>
                                        <li class="contrassegno hidden"><strong>Contrassegno:</strong> <span>€2.00</span></li>
                                        <li><strong>IVA:</strong> <span>€{{number_format((float)auth()->user()->cartItems()->with('product')->get()->sum->totalvat, 2, '.', '')}}</span></li>
                                        @if(auth()->user()->cartItems()->whereHas('discounts')->exists() && auth()->user()->cart_discount > 0)
                                            <li>
                                                <strong>Coupon applicato:</strong>
                                                <span>
                                                    - €{{ number_format(auth()->user()->cart_discount, 2, ',', '.') }}
                                                </span>                                                
                                            </li>
                                        @endif
                                        <li class="shop-cart-total shop-cart-total-no-contr"><strong>Totale:</strong> <span>
                                            €{{ number_format(auth()->user()->cart_total, 2, ',', '.') }}
                                        </span></li>
                                        <li class="shop-cart-total shop-cart-total-contr hidden"><strong>Totale:</strong> <span>
                                            €{{ number_format(auth()->user()->cart_total + 2.00, 2, ',', '.') }}
                                        </span></li>
                                    </ul>
                                    <div class="text-end mt-40">
                                        <button type="button" id="pay" class="theme-btn">Concludi Ordine<i
                                                class="fas fa-arrow-right"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!-- shop checkout end -->

    </main>
@endsection

@section('js')
    <script src="https://www.paypal.com/sdk/js?client-id=AdLiHCQZdcgZtNRYGnSfulxjw5X1cxJrb3SwXkZudYjuNCa2h104dWSJNLLFl8HmO2Gm8VIU478vX6d-&currency=EUR"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script async src="https://pay.google.com/gp/p/js/pay.js"></script>
    <script type="text/javascript">
        $( document ).ready(function() {
            // Seleziona tutti i tab
            var tabEl = document.querySelectorAll('a[data-bs-toggle="pill"]');

            tabEl.forEach(function(tab) {
                tab.addEventListener('shown.bs.tab', function(event) {
                    if (event.target.id === 'pills-tab-4') {
                        $('.contrassegno').removeClass('hidden');
                        $('.shop-cart-total-no-contr').addClass('hidden');
                        $('.shop-cart-total-contr').removeClass('hidden');
                    } else {
                        $('.contrassegno').addClass('hidden');
                        $('.shop-cart-total-no-contr').removeClass('hidden');
                        $('.shop-cart-total-contr').addClass('hidden');
                    }
                });
            });
        });


        @if ($errors->any())
            $(document).ready(function () {

                // Chiude indirizzo di spedizione
                bootstrap.Collapse.getOrCreateInstance(
                    document.getElementById('checkoutStep2'),
                    { toggle: false }
                ).hide();

                // Apre metodo di pagamento
                bootstrap.Collapse.getOrCreateInstance(
                    document.getElementById('checkoutStep3'),
                    { toggle: false }
                ).show();
            });
        @endif


        
        document.getElementById('pills-tab-1')
            .addEventListener('show.bs.tab', function (e) {

                const numeroCarte =
                    document.querySelectorAll(
                        'input[name="selected_card_id"]'
                    ).length;

                if (numeroCarte === 0) {
                    e.preventDefault();

                    document.getElementById('addCardForm').submit();
                } else {
                    const element = document.getElementById('pills-tabContent');
                    const navbarHeight = 150; // altezza navbar in px

                    window.scrollTo({
                        top: element.offsetTop - navbarHeight,
                        behavior: 'smooth'
                    });
                }
            });



        $('.payment').on('click', 'li[data-id]', function() {
            $('#paymentMethod').val($(this).attr('data-id'));
        });

        document.querySelectorAll('.tr-card').forEach(row => {

            row.addEventListener('click', function() {

                // rimuove selezione da tutte le righe
                document.querySelectorAll('.tr-card').forEach(r => {
                    r.classList.remove('tr-selected');
                });

                // aggiunge selezione alla riga cliccata
                this.classList.add('tr-selected');

                // seleziona il radio dentro la riga
                const radio = this.querySelector('.select-card');
                if (radio) {
                    radio.checked = true;
                }

            });

        });

        $(document).on('change', 'input[name="shipping_address"]', function () {

            const selectedCard = $(this).closest('.f19-address-card');

            // Aggiorna la selezione grafica
            $('#checkoutStep2 .f19-address-card')
                .removeClass('is-selected');

            selectedCard.addClass('is-selected');

            // Aggiorna il pulsante modifica
            $('#checkoutStep2 .edit-address')
                .addClass('hidden');

            selectedCard
                .find('.edit-address')
                .removeClass('hidden');

        });

        $('.card-box').on('click', function() {
            $('.card-box input[type="radio"]').prop('checked', false);
            $('.card-box').removeClass('card-box-active');

            $(this).find('input[type="radio"]').prop('checked', true);
            $(this).addClass('card-box-active');
        });

        $('#pay').on('click',function(){
            $('#payForm').submit();
        });

        $('.addCard').on('click',function(){
            $('#addCardForm').submit();
        });

        $('#applePayBtn').on('click',function(){
            $('#applePayForm').submit();
        });

        $('#googlePayBtn').on('click',function(){
            $('#gpayForm').submit();
        });

        /*paypal.Buttons({
            style: {
                layout: 'horizontal',
                color: 'gold',
                shape: 'rect',
                height: 45
            },

            // Call your server to set up the transaction
            createOrder: function(data, actions){
                return actions.order.create({
                    purchase_units:[
                        {
                            amount: {
                                value:"{{ number_format(auth()->user()->cart_total, 2, '.', '') }}"
                            }
                        }
                    ]
                })
            },
            onApprove: function(data, actions){
                // TODO send order to server
                $('.preloader').show();
                $('#paypal-order').val(data.orderID);
                $('#pay').click();
            }
        }).render("#paypalButtons");*/

        paypal.Buttons({
            style: {
                layout: 'horizontal',
                color: 'gold',
                shape: 'rect',
                height: 45
            },

            createOrder: function(data, actions) {
                return actions.order.create({
                    purchase_units: [
                        {
                            amount: {
                                value:"{{ number_format(auth()->user()->cart_total, 2, '.', '') }}"
                            }
                        }
                    ]
                });
            },

            onApprove: function(data, actions) {
                $('.preloader').show();

                $('#paypal-order').val(data.orderID);

                $('#pay').click();
            },

            onCancel: function(data) {
                $('.preloader').hide();

                console.log('Pagamento PayPal annullato', data);
            },

            onError: function(err) {
                $('.preloader').hide();

                alert('Si è verificato un errore con PayPal.');
            }

        }).render("#paypalButtons");

        document.addEventListener('DOMContentLoaded', function () {

            const applePayItem = document.getElementById('applePayItem');
            const googlePayItem = document.getElementById('googlePayItem');

            // =========================================
            // APPLE PAY
            // =========================================

            if (applePayItem) {

                let applePayAvailable = false;

                try {

                    applePayAvailable =
                        typeof window.ApplePaySession !== 'undefined' &&
                        ApplePaySession.canMakePayments();

                } catch (error) {

                    console.warn('Apple Pay non disponibile:', error);

                }

                applePayItem.style.setProperty(
                    'display',
                    applePayAvailable ? 'block' : 'none',
                    'important'
                );

            }

            // =========================================
            // GOOGLE PAY
            // =========================================

            if (googlePayItem) {

                googlePayItem.style.setProperty(
                    'display',
                    'block',
                    'important'
                );

            }

        });

    </script>

@endsection