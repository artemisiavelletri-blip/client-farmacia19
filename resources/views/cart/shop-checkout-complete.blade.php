@extends('master')

@section('content')

    <main class="main">


        <!-- shop checkout complete -->
        <div class="shop-checkout-complete py-100">
            <div class="container">
                <div class="row">
                    <div class="col-md-7 mx-auto">
                        <div class="checkout-complete-content">
                            <div class="icon-checkout">
                                <i class="far fa-check"></i>
                            </div>
                            <h3>Grazie per il tuo ordine!</h3>
                            @if($payment_method == 'bank_transfer' )
                                <p>Ti abbiamo inviato una mail con i dettagli del tuo ordine.<br><br>

                                    Ricorda che il tuo ordine sarà preso in carico alla ricezione del pagamento.<br><br>

                                    Restiamo in attesa del bonifico bancario di € {{number_format($total, 2, ',', ' ')}} su:<br>

                                    BANCA DI CREDITO COOPERATIVO DI ROMA SOCIETA' COOPERATIVA - IBAN: IT28Z0832739520000000002474, indicando come causale "Farmacia19 {{$order_number}}"
                                </p>
                            @else
                                <p>Il tuo ordine è stato effettuato e verrà elaborato il prima possibile. <br>Il numero del tuo ordine è <b>{{$order_number}}</b>.<br>Riceverai a breve un'e-mail di conferma.</p>                                
                            @endif
                            <a href="/" class="theme-btn">Torna allo shopping<i class="fas fa-arrow-right"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- shop checkout complete end -->
        
    </main>

@endsection