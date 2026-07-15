@extends('master')

@section('content')


    <main class="main">

        <!-- shop cart -->
        <div class="shop-cart py-90" id="shop-cart-id">
            <div class="container">
                <div class="shop-cart-wrap">
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="cart-table" id="cart-table-id">
                                <div class="table-responsive">
                                    @if(Auth::user() && auth()->user()->cartItems()->exists())
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
                                                @foreach(Auth::user()->cartItems as $item)
                                                    <tr>
                                                        <td>
                                                            <div class="shop-cart-img">
                                                                <a href="/shop-single/{{ $item->product()->first()->ean ?? $item->product()->first()->minsan }}"><img src="{{ asset('/storage-admin/' . $item->product()->first()->image ) }}" alt="#" onerror="this.onerror=null;this.src='{{ addslashes(asset('/storage-admin/products/file-non-disponibile.jpg')) }}';"></a>
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
                                                            <div class="shop-cart-qty shop-cart-qty-laptop" data-id="{{ $item->id }}">
                                                                <button type="button" class="minus-btn" data-id="{{ $item->id }}"><i class="fal fa-minus"></i></button>
                                                                <input class="quantity" type="text" value="{{ $item->quantity }}" 
                                                                       data-id="{{ $item->id }}" 
                                                                       max="{{ $item->quantity + $item->product->stock }}" readonly>
                                                                <button type="button" class="plus-btn" data-id="{{ $item->id }}"><i class="fal fa-plus"></i></button>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="shop-cart-subtotal">
                                                                <span class="subtotalPrice" data-id="{{ $item->id }}">€{{number_format((float)auth()->user()->cartItems()->with('product')->find($item->id)->subtotalnodiscount, 2, '.', '')}}</span>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <a href="#" class="shop-cart-remove cart-remove" data-id="{{$item->id}}"><i class="far fa-times" style="margin-top: 8px;"></i></a>
                                                        </td>
                                                    </tr>                                            
                                                @endforeach
                                            </tbody>
                                        </table>
                                    @endif
                                </div>
                                <div class="cart-mobile">
                                    @if(Auth::user() && Auth::user()->cartItems)
                                        @foreach(Auth::user()->cartItems as $item)
                                            @php
                                                $product = $item->product()->first();
                                            @endphp
                                            <div class="cart-item">
                                                <div class="cart-item-img">
                                                    <a href="/shop-single/{{ $product->ean ?? $product->minsan }}">
                                                        <img src="{{ asset('/storage-admin/' . $product->image) }}" 
                                                             alt="{{ $product->name }}" 
                                                             onerror="this.onerror=null;this.src='{{ asset('/storage-admin/products/file-non-disponibile.jpg') }}';">
                                                    </a>
                                                </div>
                                                <div class="cart-item-details">
                                                    <h5>{{ $product->name }}</h5>
                                                    <div class="subtotal mt-30">
                                                        <b class="subtotalPrice-mobile" data-id="{{ $item->id }}">€{{ number_format((float)$item->subtotalnodiscount, 2, '.', '') }}</b>
                                                    </div>
                                                    <div class="cart-item-qty mt-30 shop-cart-qty">
                                                        <button type="button" class="minus-btn" data-id="{{ $item->id }}"><i class="fal fa-minus"></i></button>
                                                        <input type="text" class="quantity" value="{{ $item->quantity }}" data-id="{{ $item->id }}" max="{{ $item->quantity + $product->stock }}" readonly>
                                                        <button type="button" class="plus-btn" data-id="{{ $item->id }}"><i class="fal fa-plus"></i></button>
                                                        <span class="btn btn-light btn-sm cart-remove" data-id="{{ $item->id }}" style="margin-left: 20px;">Rimuovi</span>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                            @if(auth()->user()->cartItems()->exists())
                                <div class="shop-cart-footer">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="shop-cart-coupon">
                                                <div class="form-group">
                                                    <input type="text" id="coupon-code" class="form-control" placeholder="Coupon">
                                                    <button class="theme-btn coupon">Applica Coupon</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                        @if(!auth()->user()->cartItems()->exists())
                            <div class="col-lg-12">
                                <div id="shop-cart-summary" class="shop-cart-summary text-center">
                                    <i class="fa fa-shopping-cart" aria-hidden="true" style="font-size: 100px;color: var(--theme-color);"></i>
                                    <h5 class="mt-30">Il tuo carrello è attualmente vuoto. <br>Esplora i nostri prodotti per aggiungere i tuoi preferiti.</h5>
                                </div>
                            </div>
                        @endif
                        @if(auth()->user()->cartItems()->exists())
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
                                        <li><strong>IVA:</strong> <span>€{{number_format((float)auth()->user()->cartItems()->with('product')->get()->sum->totalvat, 2, '.', '')}}</span></li>
                                        @if(auth()->user()->cart_discount > 0)
                                            <li>
                                                <strong>Coupon applicato:</strong>
                                                <span>
                                                    - €{{ number_format(auth()->user()->cart_discount, 2, ',', '.') }}
                                                </span>
                                            </li>

                                            <button class="btn btn-sm theme-btn removeCoupon w-100 mb-15"
                                                    title="Rimuovi coupon">
                                                Rimuovi coupon
                                            </button>
                                        @endif
                                        <li class="shop-cart-total"><strong>Totale:</strong> <span>
                                            €{{ number_format(auth()->user()->cart_total, 2, ',', '.') }}
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

        function updateButtons($wrapper) {

            let $input = $wrapper.find('.quantity');
            let qty = parseInt($input.val());
            let max = parseInt($input.attr('max'));

            let $plus = $wrapper.find('.plus-btn');
            let $minus = $wrapper.find('.minus-btn');

            $plus.prop('disabled', qty >= max);
            $minus.prop('disabled', qty <= 1);
        }

        function updateQuantity(button) {
            let $button = $(button);

            // Evita doppie esecuzioni
            if ($button.data('loading')) {
                return;
            }

            let $wrapper = $button.closest('.shop-cart-qty');
            let $input = $wrapper.find('.quantity');

            let currentQty = parseInt($input.val()) || 0;
            let max = parseInt($input.attr('max')) || 999999;

            if ($button.hasClass('plus-btn')) {

                if (currentQty >= max) {
                    updateButtons($wrapper);
                    return;
                }

                currentQty++;

            } else {

                if (currentQty <= 1) {
                    updateButtons($wrapper);
                    return;
                }

                currentQty--;
            }

            // Aggiorna subito la UI
            $input.val(currentQty);

            $button.data('loading', true);

            $.ajax({
                url: '/cart/update-quantity/' + $input.data('id'),
                type: 'POST',
                data: {
                    quantity: currentQty,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                complete: function () {
                    $button.data('loading', false);
                },
                success: function (response) {

                    // aggiorna quantità
                    $input.val(response.quantity);

                    // aggiorna subtotale desktop
                    $('.subtotalPrice[data-id="' + response.cart_item_id + '"]')
                        .text('€' + response.subtotal);

                    // aggiorna subtotale mobile
                    $('.subtotalPrice-mobile[data-id="' + response.cart_item_id + '"]')
                        .text('€' + response.subtotal);

                    $('#cart-wrapper').load(location.href + ' #cart-wrapper > *');
                    $('#shop-cart-summary').load(location.href + ' #shop-cart-summary > *');
                    $('#shop-cart-id').load(location.href + ' #shop-cart-id > *');
                    $('#cart-mobile-counter').load(location.href + ' #cart-mobile-counter > *');
                    
                },
                error: function () {
                    if ($button.hasClass('plus-btn')) {
                        $input.val(currentQty - 1);
                    } else {
                        $input.val(currentQty + 1);
                    }
                }
            });
        }

        // UN SOLO listener
        $(document).off('click', '.plus-btn, .minus-btn');

        $(document).on('click', '.plus-btn, .minus-btn', function (e) {
            e.preventDefault();
            e.stopImmediatePropagation();
            updateQuantity(this);
        });

        $('.shop-cart-qty').each(function() {
            updateButtons($(this));
        });

        $(document).on('click', '.coupon', function (e) {
            e.preventDefault();

            $.ajax({
                url: '/add-coupon',
                type: 'POST',
                data: {
                    coupon: $('#coupon-code').val(),
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) {

                    $('#cart-wrapper').load(location.href + ' #cart-wrapper > *');
                    $('#shop-cart-summary').load(location.href + ' #shop-cart-summary > *');
                    $('#shop-cart-id').load(location.href + ' #shop-cart-id > *');
                    $('#cart-mobile-counter').load(location.href + ' #cart-mobile-counter > *');

                }
            });
        });

        $(document).on('click', '.removeCoupon', function (e) {
            e.preventDefault();

            $.ajax({
                url: '/remove-coupon',
                type: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) {

                    $('#cart-wrapper').load(location.href + ' #cart-wrapper > *');
                    $('#shop-cart-summary').load(location.href + ' #shop-cart-summary > *');
                    $('#shop-cart-id').load(location.href + ' #shop-cart-id > *');
                    $('#cart-mobile-counter').load(location.href + ' #cart-mobile-counter > *');

                }
            });
        });
    </script>

@endsection