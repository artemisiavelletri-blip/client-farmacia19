@extends('master')

@section('content')
    <div class="search-popup">
        <button class="close-search"><span class="far fa-times"></span></button>
        <form action="#">
            <div class="form-group">
                <input type="search" name="search-field" class="form-control" placeholder="Search Here..." required>
                <button type="submit"><i class="far fa-search"></i></button>
            </div>
        </form>
    </div>
    <!-- mobile popup search end -->


    <main class="main">
        <div id="cart-banner" class="banner">
            Prodotto aggiunto al carrello
            <div class="progress"></div>
        </div>
        <!-- shop single -->
        <div class="shop-single py-90">
            <div class="container">
                <div class="row">
                    <div class="col-md-9 col-lg-6 col-xxl-5">
                        <div class="shop-single-gallery product-img-single">
                            <img src="{{ asset('/storage-admin/' . $product->image ) }}" alt="#" onerror="this.onerror=null;this.src='{{ addslashes(asset('/storage-admin/products/file-non-disponibile.jpg')) }}';">
                        </div>
                    </div>
                    <div id="prod-info" class="col-md-12 col-lg-6 col-xxl-6">
                        <div class="shop-single-info">
                            <h4 class="shop-single-title">{{$product->name}}</h4>
                            <div class="shop-single-cs">                                
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="shop-single-size">
                                            <h6>Quantità</h6>
                                            <div class="shop-cart-qty">
                                                <button class="minus-btn"><i class="fal fa-minus"></i></button>
                                                <input class="quantity" id="quantity" type="text" value="1" max="{{$product->stock}}" disabled="">
                                                <button class="plus-btn"><i class="fal fa-plus"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="shop-single-sortinfo">
                                <ul>
                                    <li>Stock: <span>
                                        @if($product->stock > 0)
                                            Disponibile
                                        @else
                                            Non Disponibile
                                        @endif
                                    </span></li>
                                    <li>Ean: <span>{{$product->ean}}</span></li>
                                    <li>Minsan: <span>{{$product->minsan}}</span></li>
                                    <li>Brand: <a href="/shop-search?brand={{$product->brand()->id}}">{{$product->brand()->name}}</a></li>
                                    @if($tags->isNotEmpty())
                                        <li>Tags: 
                                            @foreach($tags as $tag)
                                                <a href="/shop-search?tag={{$tag->slug}}">{{$tag->name}}</a>
                                            @endforeach
                                        </li>
                                    @endif
                                </ul>
                            </div>
                            <div class="row">
                                <div class="col-md-12 d-flex align-items-center gap-2">
                                    @if(!$product->discountPrice)
                                        <h3 class="mb-0">€{{number_format((float)$product->price, 2, '.', '')}}</h3>
                                    @else
                                        <small class="strike mb-0">€{{number_format((float)$product->price, 2, '.', '')}}</small>
                                        <h3 class="mb-0">€{{number_format((float)$product->discountPrice, 2, '.', '')}}</h3>
                                    @endif
                                </div>
                            </div>
                            <div class="shop-single-action">
                                <div class="row align-items-center">
                                    <div class="col-md-12">
                                        <div class="shop-single-btn">
                                            @if($product->stock > 0)
                                                <span class="add-to-cart theme-btn" data-id="{{$product->id}}"><span class="far fa-shopping-bag"></span>Aggiungi al Carrello</span>
                                            @else
                                                <span class="theme-btn theme-btn-disabled" disabled><span class="far fa-shopping-bag"></span>Prodotto Non Disponibile</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- shop single details -->
                <div class="shop-single-details">
                    <nav>
                        <div class="nav nav-tabs" id="nav-tab" role="tablist">
                            <button class="nav-link active" id="nav-tab1" data-bs-toggle="tab" data-bs-target="#tab1"
                                type="button" role="tab" aria-controls="tab1" aria-selected="true">Descrizione</button>
                        </div>
                    </nav>
                    <div class="tab-content" id="nav-tabContent">
                        <div class="tab-pane fade show active" id="tab1" role="tabpanel" aria-labelledby="nav-tab1">
                            <div class="shop-single-desc">
                                <p>
                                    {!! html_entity_decode($product->description) !!}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- shop single details end -->

            
                <!-- related item -->
                @if($relatedProducts->isNotEmpty())
                    <div class="product-area related-item pt-40">
                        <div class="container px-0">
                            <div class="row">
                                <div class="col-12">
                                    <div class="site-heading-inline">
                                        <h2 class="site-title">Prodotti Correlati</h2>
                                    </div>
                                </div>
                            </div>
                            <div class="row g-4 item-2">
                                @foreach($relatedProducts as $relatedProduct)
                                    <div class="col-md-3">
                                        <div class="product-item">
                                            <div class="product-img">
                                                <a href="/shop-single/{{ !empty($relatedProduct->minsan) ? $relatedProduct->minsan : $relatedProduct->ean }}"><img src="{{asset('/storage-admin/' . $relatedProduct->image) }}" alt="" onerror="this.onerror=null;this.src='{{ addslashes(asset('/storage-admin/products/file-non-disponibile.jpg')) }}';"></a>
                                                <div class="product-action-wrap">
                                                    <div class="product-action">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="product-content">
                                                <h3 class="product-title"><a href="/shop-single/{{ !empty($relatedProduct->minsan) ? $relatedProduct->minsan : $relatedProduct->ean }}">{{$relatedProduct->name}}</a></h3>
                                                <div class="product-bottom">
                                                    <div class="product-price">
                                                        @if(!$relatedProduct->discountPrice)
                                                            <span>€{{ number_format($relatedProduct->price, 2, '.', '') }}</span>
                                                        @else
                                                            <small class="strike">
                                                                €{{ number_format($relatedProduct->price, 2, '.', '') }}
                                                            </small>
                                                            <span>
                                                                €{{ number_format($relatedProduct->discountPrice, 2, '.', '') }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="shop-single-action">
                                                    <div class="row align-items-center text-center">
                                                        <div class="col-md-12">
                                                            <div class="shop-single-btn">
                                                                <a href="/shop-single/{{ !empty($relatedProduct->minsan) ? $relatedProduct->minsan : $relatedProduct->ean }}" class="theme-btn">Vai al Prodotto</a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
                <!-- related item end -->
            </div>
        </div>
        <!-- shop single end -->

    </main>
@endsection

@section('js')
    <script type="text/javascript">
        $('.add-to-cart').on('click', function () {

            let productId = $(this).data('id');
            let qta = $('#quantity').val();

            $.ajax({
                url: '/cart/add',
                type: 'POST',
                data: {
                    product_id: productId,
                    quantity: qta,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) {
                    $('#cart-wrapper').load(window.location.href + ' #cart-wrapper > *');
                    $('#prod-info').load(window.location.href + ' #prod-info > *');
                    $('#shop-cart-id').load(window.location.href + ' #shop-cart-id > *');
                    $('#cart-mobile-counter').load(window.location.href + ' #cart-mobile-counter > *');0
                    const banner = document.getElementById('cart-banner');
                    banner.classList.add('show');

                    setTimeout(() => {
                        banner.classList.remove('show');
                    }, 3000);
                },
                error: function (xhr) {
                    if (xhr.status === 401) {
                        // Non loggato → redirect al login
                        window.location.href = '/login';
                    }
                }
            });
        });
    </script>
@endsection