@extends('master')

@section('content')


    <main class="main">

        <!-- shop cart -->
        <div class="shop-cart py-90">
            <div class="container">
                <div class="shop-cart-wrap">
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="cart-table">
                                <div class="table-responsive">
                                    <table class="table" id="table-cart-wrapper">
                                        <thead>
                                            <tr>
                                                <th>Immagine</th>
                                                <th>Nome Prodotto</th>
                                                <th>Prezzo</th>
                                                <th>Quantità</th>
                                                <th>Sub Totale</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @if(Auth::user() && Auth::user()->cartItems)
                                                @foreach(Auth::user()->cartItems as $item)
                                                    <tr>
                                                        <td>
                                                            <div class="shop-cart-img">
                                                                <a href="/shop-single/{{ $item->product()->first()->ean ?? $item->product()->first()->minsan }}"><img src="{{ asset('/storage-admin/' . $item->product()->first()->image ) }}" alt="#"></a>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="shop-cart-content">
                                                                <h5 class="shop-cart-name"><a href="/shop-single/{{ $item->product()->first()->ean ?? $item->product()->first()->minsan }}">{{$item->product()->first()->name}}</a></h5>
                                                                <div class="shop-cart-info">
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="shop-cart-price">
                                                                <span>
                                                                    @if($item->product()->first()->discountPrice)
                                                                        €{{number_format((float)$item->product()->first()->discountPrice, 2, '.', '')}}
                                                                    @else
                                                                        €{{number_format((float)$item->product()->first()->price, 2, '.', '')}}
                                                                    @endif
                                                                </span>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="shop-cart-qty">
                                                                <button class="minus-btn" data-id="{{ $item->id }}"><i class="fal fa-minus"></i></button>
                                                                <input class="quantity" type="text" value="{{ $item->quantity }}" 
                                                                       data-id="{{ $item->id }}" 
                                                                       max="{{ $item->quantity + $item->product->stock }}">
                                                                <button class="plus-btn" data-id="{{ $item->id }}"><i class="fal fa-plus"></i></button>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="shop-cart-subtotal">
                                                                <span>€{{number_format((float)auth()->user()->cartItems()->with('product')->find($item->id)->subtotal, 2, '.', '')}}</span>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <a href="#" class="shop-cart-remove cart-remove" data-id="{{$item->id}}"><i class="far fa-times" style="margin-top: 8px;"></i></a>
                                                        </td>
                                                    </tr>                                            
                                                @endforeach
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <!-- <div class="shop-cart-footer">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="shop-cart-coupon">
                                            <div class="form-group">
                                                <input type="text" class="form-control" placeholder="Coupon">
                                                <button class="theme-btn" type="submit">Applica Coupon</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div> -->
                        </div>
                        @if(auth()->user()->cartItems()->exists())
                            <div class="col-lg-4">
                                <div id="shop-cart-summary" class="shop-cart-summary">
                                    <h5>Riepilogo Carrello</h5>
                                    <ul>
                                        <li><strong>Sub Totale:</strong> <span>€{{number_format((float)auth()->user()->cartItems()->with('product')->get()->sum->subtotalnoiva, 2, '.', '')}}</span></li>
                                        <!-- <li><strong>Discount:</strong> <span>$5.00</span></li> -->
                                        <li><strong>Spese di Spedizione:</strong> <span>
                                            @if(number_format((float)auth()->user()->cartItems()->with('product')->get()->sum->subtotal, 2, '.', '') > 49.90)
                                                Gratuite
                                            @else
                                                €5.90
                                            @endif
                                        </span></li>
                                        <li><strong>IVA:</strong> <span>€{{number_format((float)auth()->user()->cartItems()->with('product')->get()->sum->totalvat, 2, '.', '')}}</span></li>
                                        <li class="shop-cart-total"><strong>Totale:</strong> <span>
                                            @if(number_format((float)auth()->user()->cartItems()->with('product')->get()->sum->subtotal, 2, '.', '') > 49.90)
                                                €{{number_format((float)auth()->user()->cartItems()->with('product')->get()->sum->subtotal, 2, '.', '')}}
                                            @else
                                                €{{number_format((float)(auth()->user()->cartItems()->with('product')->get()->sum->subtotal + 5.90), 2, '.', '')}}
                                            @endif
                                        </span></li>
                                    </ul>
                                    <div class="text-end mt-40">
                                        <a href="/shop-checkout" class="theme-btn">Checkout<i
                                                class="fas fa-arrow-right"></i></a>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <!-- shop cart end -->

    </main>

@endsection

@section('js')
    
    <script type="text/javascript">

        function updateQuantity(button) {
            let $button = $(button);
            let $wrapper = $button.closest('.shop-cart-qty'); // solo il container del prodotto
            let $input = $wrapper.find('.quantity'); // input corretto
            let currentQty = parseInt($input.val());
            let max = parseInt($input.attr('max'));

            if ($button.hasClass('plus-btn')) {
                if (currentQty > max) return;
            } else {
                if (currentQty < 1) return;
            }

            // Aggiorna input per UX
            $input.val(currentQty);
            // AJAX per aggiornare carrello
            $.ajax({
                url: '/cart/update-quantity/' + $input.data('id'),
                type: 'POST',
                data: {
                    quantity: currentQty,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    // Ricarica solo le sezioni interessate
                    $('#cart-wrapper').load(window.location.href + ' #cart-wrapper > *');
                    $('#table-cart-wrapper').load(window.location.href + ' #table-cart-wrapper > *');
                    $('#shop-cart-summary').load(window.location.href + ' #shop-cart-summary > *');
                },
                error: function(xhr) {
                    // rollback in caso di errore
                    $input.val(currentQty - ($button.hasClass('plus-btn') ? 1 : -1));
                }
            });
        }

        $('.minus-btn').on('click',function(){
            updateQuantity(this);
        });

        $(document).ready(function() {
    $(document).on('click', '.plus-btn, .minus-btn', function() {
        updateQuantity(this); // qui richiamo la funzione
    });
});


        $(document).on('click', '.plus-btn, .minus-btn', function() {
            let $button = $(this);
            let $wrapper = $button.closest('.shop-cart-qty'); // solo il container del prodotto
            let $input = $wrapper.find('.quantity'); // input corretto
            let currentQty = parseInt($input.val());
            let max = parseInt($input.attr('max'));

            if ($button.hasClass('plus-btn')) {
                if (currentQty > max) return;
                currentQty++;
            } else {
                if (currentQty < 1) return;
                currentQty--;
            }

            // Aggiorna input per UX
            $input.val(currentQty);

            // AJAX per aggiornare carrello
            $.ajax({
                url: '/cart/update-quantity/' + $input.data('id'),
                type: 'POST',
                data: {
                    quantity: currentQty,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    $('#cart-wrapper').load(window.location.href + ' #cart-wrapper > *');
                    $('#table-cart-wrapper').load(window.location.href + ' #table-cart-wrapper > *');
                    $('#shop-cart-summary').load(window.location.href + ' #shop-cart-summary > *');
                },
                error: function(xhr) {
                    $input.val(currentQty - ($button.hasClass('plus-btn') ? 1 : -1));
                }
            });
        });
    </script>

@endsection