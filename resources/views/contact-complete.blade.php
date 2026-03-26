@extends('master')

@section('content')

    <main class="main">


        <!-- shop checkout complete -->
        <div class="shop-checkout-complete py-100">
            <div class="container">
                <div class="row">
                    <div class="col-md-7 mx-auto">
                        <div class="checkout-complete-content">
                            @if (session()->has('error'))
                                <div class="icon-checkout error-color">
                                    <i class="fa-solid fa-x"></i>
                                </div>
                                <h3>Richiesta di contatto!</h3>
                                <p>Non siamo riusciti a elaborare la tua richiesta.<br>Per favore riprova più tardi.<br><br>Se il problema dovesse persistere ti preghiamo di contattarci alla seguente email: <b>info@farmacia19.it</b></p>
                            @endif

                            @if (session()->has('success'))
                                <div class="icon-checkout">
                                    <i class="far fa-check"></i>
                                </div>
                                <h3>Richiesta di contatto!</h3>
                                <p>Grazie! La tua richiesta è stata inviata correttamente.<br>Ti risponderemo al più presto.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- shop checkout complete end -->
        
    </main>

@endsection