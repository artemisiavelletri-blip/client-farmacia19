@extends('master')

<?php

    // Pagamento OneClik - Primo pagamento - Avvio pagamento

    // Alias e chiave segreta 
    $ALIAS = env('XPAY_ALIAS'); // Sostituire con il valore fornito da Nexi
    $CHIAVESEGRETA = env('XPAY_SECRET'); // Sostituire con il valore fornito da Nexi

    $requestUrl = env('NEXI_BASE_URL');
    //$merchantServerUrl = "http://" . $_SERVER['HTTP_HOST'] . "/cards/";
    $merchantServerUrl = "https://" . $_SERVER['HTTP_HOST'];

    $codTrans = "PS" . date('YmdHis');
    $divisa = "EUR";
    $importo = 0;

    // Calcolo MAC
    $mac = sha1('codTrans=' . $codTrans . 'divisa=' . $divisa . 'importo=' . $importo . $CHIAVESEGRETA);

    $numContratto = "NC_" . date('YmdHis');
    $tipoRichiesta = 'PP';

    // Parametri obbligatori
    $obbligatori = array(
        'alias' => $ALIAS,
        'importo' => $importo,
        'divisa' => $divisa,
        'codTrans' => $codTrans,
        'url' => $merchantServerUrl . "/payment-method/store",
        'url_back' => $merchantServerUrl . "/shop-checkout",
        'mac' => $mac,
        'num_contratto' => $numContratto,
        'tipo_servizio' => 'paga_oc3d',
        'tipo_richiesta' => $tipoRichiesta,
        );

    // Parametri facoltativi
    $facoltativi = array(
    );

    $requestParams = array_merge($obbligatori, $facoltativi);

    ?>

    <form method='POST' id="addCardForm" action='<?php echo $requestUrl ?>'>
        <?php foreach ($requestParams as $name => $value) { ?>
            <input type='hidden' name='<?php echo $name; ?>' value='<?php echo htmlentities($value); ?>' />
        <?php } ?>
    </form>

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
                        <div class="row">
                            <div class="col-lg-8">
                                <div class="shop-checkout-step">
                                    <div class="accordion" id="shopCheckout">
                                        <div class="accordion-item">
                                          <h2 class="accordion-header">
                                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#checkoutStep1" aria-expanded="true" aria-controls="checkoutStep1">
                                                Indirizzo di Fatturazione
                                            </button>
                                          </h2>
                                          <div id="checkoutStep1" class="accordion-collapse collapse show" data-bs-parent="#shopCheckout">
                                            <div class="accordion-body">
                                                <div class="row shop-checkout-form">
                                                    <div class="col-8">
                                                        @if($user->user_type)
                                                            <b>Ragione Sociale: </b>{{$user->company_society}}<br>
                                                        @endif
                                                        <b>Nome: </b>{{$user->name}}<br>
                                                        <b>Cognome: </b>{{$user->surname}}<br>
                                                        <b>Indirizzo: </b>{{$user->billingAddresses()->first()->address}}<br>
                                                        <b>Note: </b>{{$user->billingAddresses()->first()->note}}<br>
                                                        <b>CAP: </b>{{$user->billingAddresses()->first()->cap}}<br>
                                                        <b>Città (Provincia): </b>{{$user->billingAddressesCity()->name . ' (' . $user->billingAddressesCity()->province . ')'}}<br>
                                                        <b>Telefono: </b>{{$user->billingAddresses()->first()->phone}}<br>
                                                        @if(!$user->user_type)
                                                            <b>C.F.: </b>{{$user->cf}}<br>
                                                        @else
                                                            <b>C.F.: </b>{{$user->company_cf}}<br>
                                                            <b>P.Iva: </b>{{$user->company_pi}}<br>
                                                            <b>Pec: </b>{{$user->company_pec}}<br>
                                                            <b>Codice SDI: </b>{{$user->company_sdi}}<br>
                                                        @endif
                                                    </div>
                                                    <div class="col-4 align-right">
                                                        <a href="/settings/billing-address"><i class="fas fa-edit"></i></a>
                                                    </div>
                                                </div> 
                                            </div>
                                          </div>
                                        </div>
                                        <div class="accordion-item">
                                          <h2 class="accordion-header">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#checkoutStep2" aria-expanded="false" aria-controls="checkoutStep2">
                                                Indirizzo di Spedizione
                                            </button>
                                          </h2>
                                          <div id="checkoutStep2" class="accordion-collapse collapse" data-bs-parent="#shopCheckout">
                                            <div class="accordion-body">
                                                <div class="row shop-checkout-form">
                                                    <div class="col-8"> 
                                                        @if($user->shippingAddresses())
                                                            <b>Nome: </b>{{$user->shippingAddresses()->first()->recipient_name}}<br>
                                                            <b>Indirizzo: </b>{{$user->shippingAddresses()->first()->address}}<br>
                                                            <b>Note: </b>{{$user->shippingAddresses()->first()->note}}<br>
                                                            <b>CAP: </b>{{$user->shippingAddresses()->first()->cap}}<br>
                                                            <b>Città (Provincia): </b>{{$user->shippingAddressesCity()->name . ' (' . $user->shippingAddressesCity()->province . ')'}}<br>
                                                            <b>Telefono: </b>{{$user->shippingAddresses()->first()->phone}}<br>
                                                        @else
                                                            <b>Nome: </b>{{$user->name . ' ' . $user->surname}}<br>
                                                            <b>Indirizzo: </b>{{$user->billingAddresses()->first()->address}}<br>
                                                            <b>Note: </b>{{$user->billingAddresses()->first()->note}}<br>
                                                            <b>CAP: </b>{{$user->billingAddresses()->first()->cap}}<br>
                                                            <b>Città (Provincia): </b>{{$user->billingAddressesCity()->name . ' (' . $user->billingAddressesCity()->province . ')'}}<br>
                                                            <b>Telefono: </b>{{$user->billingAddresses()->first()->phone}}<br>
                                                        @endif
                                                        <div class="shop-checkout-payment mt-10">
                                                            <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
                                                                <li class="nav-item" role="presentation">
                                                                    <a class="nav-link active" id="pills-tab-0" data-bs-toggle="pill"
                                                                        data-bs-target="#pills-0" type="button" role="tab" aria-controls="pills-0"
                                                                        aria-selected="true">
                                                                        <div class="checkout-card-img">
                                                                            <img src="{{asset('/img/shipping.png')}}" alt="">
                                                                        </div>
                                                                        <span><b>1-2 Giorni</b></span>
                                                                        @if(number_format((float)auth()->user()->cartItems()->with('product')->get()->sum->subtotal, 2, '.', '') <= 49.90)
                                                                            <span><b>€5.90</b></span>
                                                                        @endif
                                                                    </a>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                    <div class="col-4 align-right">
                                                        <a href="/settings/shipping-address"><i class="fas fa-edit"></i></a>
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
                                                        <li class="nav-item" role="presentation" data-id="stripe" style="width:100%!important">
                                                            <a class="nav-link" id="pills-tab-1" data-bs-toggle="pill"
                                                                data-bs-target="#pills-1" type="button" role="tab" aria-controls="pills-1"
                                                                aria-selected="true" style="text-align: left!important;">
                                                                <div class="checkout-card-img" style="display: inline;">
                                                                    <img src="{{ asset('/img/payment/cards.png') }}" alt="" style="width: 80px!important;">
                                                                </div>
                                                                <span>Paga con Carta</span>
                                                            </a>
                                                        </li>
                                                        <li class="nav-item" role="presentation" data-id="bank_transfer" style="width:100%!important">
                                                            <a class="nav-link" id="pills-tab-3" data-bs-toggle="pill"
                                                                data-bs-target="#pills-3" type="button" role="tab" aria-controls="pills-3"
                                                                aria-selected="false" style="text-align: left!important;">
                                                                <div class="checkout-payment-img" style="display: inline;">
                                                                    <img src="{{ asset('/img/payment/bonifico.png') }}" alt="" style="width: 80px!important;">
                                                                </div>
                                                                <span>Paga con Bonifico</span>
                                                            </a>
                                                        </li>
                                                        <li class="nav-item" role="presentation" data-id="cod" style="width:100%!important">
                                                            <a class="nav-link" id="pills-tab-4" data-bs-toggle="pill"
                                                                data-bs-target="#pills-4" type="button" role="tab" aria-controls="pills-4"
                                                                aria-selected="false" style="text-align: left!important;">
                                                                <div class="checkout-payment-img cod" style="display: inline;">
                                                                    <img src="{{ asset('/img/payment/cash.png') }}" alt="" style="width: 80px!important;padding: 10px!important;">
                                                                </div>
                                                                <span>Paga alla Consegna</span>
                                                            </a>
                                                        </li>
                                                    </ul>
                                                    <div class="tab-content" id="pills-tabContent">
                                                        <div class="tab-pane fade show" id="pills-1" role="tabpanel"
                                                            aria-labelledby="pills-tab-1" tabindex="0">
                                                            <div class="shop-checkout-form">
                                                                <div class="table-responsive">
                                                                    <table class="table table-borderless text-nowrap">
                                                                        <thead>
                                                                            <tr>
                                                                                <th>Info Carta</th>
                                                                                <th>Nome Carta</th>
                                                                                <th>Numero Carta</th>
                                                                                <th>Data Scadenza</th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                            @foreach($user->paymentMethods()->get() as $card)
                                                                                <tr class="tr-card {{ $loop->first ? 'tr-selected' : '' }}">
                                                                                    <td>
                                                                                        <input type="radio" name="selected_card_id" value="{{ $card->id }}" class="select-card hidden" {{ $loop->first ? 'checked' : '' }}>
                                                                                        <div class="table-list-img w-25">
                                                                                            @if($card->brand == 'visa')
                                                                                                <img class="rounded-3" src="{{ asset('/img/payment/visa.svg') }}" alt="">
                                                                                            @elseif($card->brand == 'mastercard')
                                                                                                <img class="rounded-3" src="{{ asset('/img/payment/mastercard.png') }}" alt="">
                                                                                            @else
                                                                                                <img class="rounded-3" src="{{ asset('/img/payment/mastercard.png') }}" alt="">
                                                                                            @endif
                                                                                        </div>
                                                                                    </td>
                                                                                    <td><span class="table-list-code">{{$card->holder_name}}</span></td>
                                                                                    <td>***********{{$card->last4}}</td>
                                                                                    <td>{{$card->exp_month}}/{{$card->exp_year}}</td>
                                                                                </tr>
                                                                            @endforeach
                                                                        </tbody>
                                                                    </table>
                                                                    <div class="text-center">
                                                                        <button class="theme-btn addCard" type="button"><span class="far fa-plus-circle"></span>Aggiungi Carta</button>
                                                                    </div>
                                                                </div>
                                                                <div class="cart-mobile">
                                                                    <h5 class="mb-3">Seleziona carta</h5>
                                                                    @foreach($user->paymentMethods()->get() as $card)
                                                                        <div class="card-box {{ $loop->first ? 'card-box-active' : '' }}">
                                                                            <input type="radio" name="selected_card_id" value="{{ $card->id }}" class="select-card-mobile hidden" {{ $loop->first ? 'checked' : '' }}>
                                                                            <div class="card-box-img">
                                                                                @if($card->brand == 'visa')
                                                                                    <img class="rounded-3" style="width: 80px!important;" src="{{ asset('/img/payment/visa.svg') }}" alt="">
                                                                                @elseif($card->brand == 'mastercard')
                                                                                    <img class="rounded-3" style="width: 80px!important;" src="{{ asset('/img/payment/mastercard.png') }}" alt="">
                                                                                @else
                                                                                    <img class="rounded-3" src="{{ asset('/img/payment/mastercard.png') }}" alt="">
                                                                                @endif
                                                                            </div>
                                                                            <div class="card-box-info">
                                                                                <h5 class="table-list-code">{{$card->holder_name}}</h5>
                                                                                <h5>***********{{$card->last4}}</h5>
                                                                                <h5>{{$card->exp_month}}/{{$card->exp_year}}</h5>
                                                                            </div>
                                                                        </div>
                                                                    @endforeach
                                                                    <div class="text-center">
                                                                        <button class="theme-btn addCard" type="button"><span class="far fa-plus-circle"></span>Aggiungi Carta</button>
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
                                            @if(auth()->user()->cart_total > 49.90)
                                                Gratuite
                                            @else
                                                €5.90
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
                                            <button class="btn btn-sm theme-btn removeCoupon w-100 mb-15" title="Rimuovi coupon">
                                                Rimuovi coupon
                                            </button>
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
    <script src="https://www.paypal.com/sdk/js?client-id=Aa1SWEv2p4a-N6KZVeBNzGdwsueD_JNKfBSFj-SCsuJwgOIqLv428IiSZbcPm3MPtkXyOPQn43goUqGU&currency=EUR"></script>
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

        $('.payment').on('click', 'li', function() {
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

        $('.card-box').on('click', function() {
            $('.card-box input[type="radio"]').prop('checked', false);
            $('.card-box').removeClass('card-box-active');

            $(this).find('input[type="radio"]').prop('checked', true);
            $(this).addClass('card-box-active');
        });

        $('#pills-tab-1').on('click',function(){
            const element = document.getElementById('pills-tabContent');
            const navbarHeight = 150; // altezza navbar in px

            window.scrollTo({
                top: element.offsetTop - navbarHeight,
                behavior: 'smooth'
            });
        })

        $('#pay').on('click',function(){
            $('#payForm').submit();
        });

        $('.addCard').on('click',function(){
            $('#addCardForm').submit();
        });

        paypal.Buttons({

            createOrder() {
                return fetch('/paypal/order/create', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document
                            .querySelector('meta[name="csrf-token"]')
                            .content
                    }
                })
                .then(res => res.json())
                .then(data => data.id);
            },

            onApprove(data) {

                return fetch(`/paypal/order/${data.orderID}/capture`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document
                            .querySelector('meta[name="csrf-token"]')
                            .content
                    }
                })
                .then(res => res.json())
                .then(orderData => {
                    console.log(orderData);
                });
            }

        }).render('#paypal-button-container');
    </script>

@endsection