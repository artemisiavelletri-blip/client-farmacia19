@extends('master')

@section('content')
    <main class="main">

        <!-- hero slider -->

        <div id="carouselExampleIndicators" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-indicators">
                @foreach($sliders as $slider)
                    <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="{{ $loop->index }}" class="{{ $loop->first ? 'active' : '' }}" aria-current="true" aria-label="Slide {{ $loop->index + 1 }}"></button>
                @endforeach
            </div>
            <div class="carousel-inner">
                @foreach($sliders as $slider)
                    <div class="carousel-item {{ $loop->first ? 'active' : '' }}">
                        <img src="{{asset('/storage-admin/' . $slider->path) }}" class="d-block w-100" alt="...">
                    </div>
                @endforeach
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        </div>

        
        <!-- hero slider end -->


        <!-- category area -->
        <div class="category-area2 pt-80 pb-100">
            <div class="container">
                <div class="category-slider owl-carousel owl-theme wow fadeInUp" data-wow-delay=".25s">
                    @foreach($categories as $category)
                        <div class="category-item">
                            <a href="/shop-grid/{{$category->token}}">
                                <div class="category-info">
                                    <div class="icon">
                                        <img src="{{asset('/storage-admin/' . $category->image)}}" alt="">
                                    </div>
                                    <div class="content">
                                        <h4 class="category-title">{{$category->name}}</h4>
                                        <p>{{$category->products()->count()}} Prodotti</p>
                                    </div>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        <!-- category area end-->

        <!-- feature area -->
        <div class="feature-area pb-100">
            <div class="container wow fadeInUp" data-wow-delay=".25s">
                <div class="feature-wrap">
                    <div class="row g-0">
                        <div class="col-12 col-md-6 col-lg-3">
                            <div class="feature-item">
                                <div class="feature-icon">
                                    <i class="mt-15 fal fa-truck"></i>
                                </div>
                                <div class="feature-content">
                                    <h4>Spedizione Gratuita</h4>
                                    <p>Ordini superiori a €49,90</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-3">
                            <div class="feature-item">
                                <div class="feature-icon">
                                    <i class="mt-15 fal fa-sync"></i>
                                </div>
                                <div class="feature-content">
                                    <h4>Resi</h4>
                                    <p>Resi entro 30 giorni</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-3">
                            <div class="feature-item">
                                <div class="feature-icon">
                                    <i class="mt-15 fal fa-wallet"></i>
                                </div>
                                <div class="feature-content">
                                    <h4>Pagamenti Sicuri</h4>
                                    <p>100% Pagamenti sicuri</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-3">
                            <div class="feature-item">
                                <div class="feature-icon">
                                    <i class="mt-15 fal fa-headset"></i>
                                </div>
                                <div class="feature-content">
                                    <h4>Supporto</h4>
                                    <p>Chatta con noi</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- feature area end -->


        <!-- small banner -->
        <div class="small-banner pb-100">
            <div class="container wow fadeInUp" data-wow-delay=".25s">
                <div class="row g-4">
                    <!-- @foreach($promotions as $promotion)
                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="banner-item">
                                <img src="{{asset('/storage-admin/' . $promotion->path)}}" alt="">
                            </div>
                        </div>
                    @endforeach -->
                    <div class="col-12 col-md-6 col-lg-4 text-center">
                        <img class="img-small-banner" src="{{ asset('/img/doctor.png') }}">
                        <h4 class="mt-15 mb-15">PROFESSIONALITA'</h4>
                        <span>Rapporto di massima disponibilità con i clienti</span>
                    </div>
                    <div class="col-12 col-md-6 col-lg-4 text-center">
                        <img class="img-small-banner" src="{{ asset('/img/shield.png') }}">
                        <h4 class="mt-15 mb-15">AFFIDABILITA'</h4>
                        <span>Garantisce sempre un livello di servizio elevato</span>
                    </div>
                    <div class="col-12 col-md-6 col-lg-4 text-center">
                        <img class="img-small-banner" src="{{ asset('/img/pharmacy.png') }}">
                        <h4 class="mt-15 mb-15">AL SERVIZIO DELLA SALUTE</h4>
                        <span>Con i migliori prodotti sul mercato e al miglior prezzo</span>
                    </div>
                </div>
            </div>
        </div>
        <!-- small banner end -->       


        <!-- popular item -->
        <div class="product-area pb-100">
            <div class="container">
                <div class="row g-4">
                    <div class="col-lg-12">
                        <div class="row">
                            <div class="col-12 wow fadeInDown" data-wow-delay=".25s">
                                <div class="site-heading-inline">
                                    <h2 class="site-title">Bestseller</h2>
                                </div>
                                <div class="item-tab">
                                    <ul class="nav nav-pills mt-40 mb-50" id="item-tab" role="tablist">
                                        @foreach($categories as $category)
                                            <li class="nav-item" role="presentation">
                                                <button class="nav-link {{ $loop->first ? 'active' : '' }}" id="item-tab{{ $loop->index + 1 }} " data-bs-toggle="pill"
                                                    data-bs-target="#pill-item-tab{{ $loop->index + 1 }} " type="button" role="tab"
                                                    aria-controls="pill-item-tab{{ $loop->index + 1 }} " aria-selected="{{ $loop->first ? 'true' : 'false' }}">{{$category->name}}</button>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                        @foreach($categories as $category)
                            <div class="tab-content wow fadeInUp" data-wow-delay=".25s" id="item-tabContent">
                                <div class="tab-pane show {{ $loop->first ? 'active' : '' }}" id="pill-item-tab{{ $loop->index + 1 }}" role="tabpanel" aria-labelledby="item-tab{{ $loop->index + 1 }}"
                                    tabindex="0">
                                    <div class="row g-3 item-3">
                                        @foreach($category->productsBestseller as $prodBest)
                                            <div class="col-md-6 col-lg-4 col-xl-3">
                                                <div class="product-item">
                                                    <div class="product-img">
                                                         @if($prodBest->discountPrice)
                                                            <span class="type discount">-{{round((($prodBest->price - $prodBest->discountPrice) / $prodBest->price) * 100)}}%</span>
                                                        @endif
                                                        <a href="/shop-single/{{ $prodBest->ean ?? $prodBest->minsan }}">
                                                            <img src="{{asset('/storage-admin/' . $prodBest->image) }}" alt="">
                                                        </a>
                                                    </div>
                                                    <div class="product-content">
                                                        <h3 class="product-title"><a href="/shop-single/{{ $prodBest->ean ?? $prodBest->minsan }}">
                                                            {{ $prodBest->name }}
                                                        </a></h3>
                                                        <div class="product-bottom">
                                                            <div class="product-price">
                                                                @if(!$prodBest->discountPrice)
                                                                    <span>€{{ number_format($prodBest->price, 2, '.', '') }}</span>
                                                                @else
                                                                    <small class="strike">
                                                                        €{{ number_format($prodBest->price, 2, '.', '') }}
                                                                    </small>
                                                                    <span>
                                                                        €{{ number_format($prodBest->discountPrice, 2, '.', '') }}
                                                                    </span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                        <div class="shop-single-action">
                                                            <div class="row align-items-center text-center">
                                                                <div class="col-md-12">
                                                                    <div class="shop-single-btn">
                                                                        <a href="/shop-single/{{ $prodBest->ean ?? $prodBest->minsan }}" class="theme-btn">Vai al Prodotto</a>
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
                        @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- popular item end -->

        <div class="big-banner pb-100">
            <div class="container wow fadeInUp" data-wow-delay=".25s" style="visibility: visible; animation-delay: 0.25s; animation-name: fadeInUp;">
                <div class="banner-wrap" style="background-image: url({{ asset('img/product/free_shipping_2.png') }});">
                    <div class="row">
                        <div class="col-lg-8 mx-auto">
                            <div class="banner-content">
                                <div class="banner-info mb-50">
                                    <h6>SPEDIZIONE GRATUITA</h6>
                                    <h2>Per ordini superiori a </h2>
                                    <h2>€49.90</h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <!-- trending item -->
        <div class="product-area pb-100">
            <div class="container">
                <div class="row">
                    <div class="col-12 wow fadeInDown" data-wow-delay=".25s">
                        <div class="site-heading-inline">
                            <h2 class="site-title">Prodotti in Promozione</h2>
                        </div>
                    </div>
                </div>
                <div class="product-wrap item-3 wow fadeInUp" data-wow-delay=".25s">
                    <div class="product-slider owl-carousel owl-theme">
                        @foreach($productsDiscounted as $prodDisc)
                            <div class="product-item">
                                <div class="product-img">
                                     @if($prodDisc->discountPrice)
                                        <span class="type discount">-{{round((($prodDisc->price - $prodDisc->discountPrice) / $prodDisc->price) * 100)}}%</span>
                                    @endif
                                    <a href="/shop-single/{{ $prodDisc->ean ?? $prodDisc->minsan }}">
                                        <img src="{{asset('/storage-admin/' . $prodDisc->image) }}" alt="">
                                    </a>
                                </div>
                                <div class="product-content">
                                    <h3 class="product-title"><a href="/shop-single/{{ $prodDisc->ean ?? $prodDisc->minsan }}">
                                        {{ $prodDisc->name }}
                                    </a></h3>
                                    <div class="product-bottom">
                                        <div class="product-price">
                                            @if(!$prodDisc->discountPrice)
                                                <span>€{{ number_format($prodDisc->price, 2, '.', '') }}</span>
                                            @else
                                                <small class="strike">
                                                    €{{ number_format($prodDisc->price, 2, '.', '') }}
                                                </small>
                                                <span>
                                                    €{{ number_format($prodDisc->discountPrice, 2, '.', '') }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="shop-single-action">
                                        <div class="row align-items-center text-center">
                                            <div class="col-md-12">
                                                <div class="shop-single-btn">
                                                    <a href="/shop-single/{{ $prodDisc->ean ?? $prodDisc->minsan }}" class="theme-btn">Vai al Prodotto</a>
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
        </div>
        <!-- trending item end -->
    </main>

    


    


    <!-- modal quick shop-->
    <div class="modal quickview fade" id="quickview" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="quickview" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><i
                        class="far fa-xmark"></i></button>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-lg-6 col-md-12 col-sm-12 col-xs-12">
                            <div class="quickview-img">
                                <img src="{{ asset('/img/product/04.png') }}" alt="#">
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-12 col-sm-12 col-xs-12">
                            <div class="quickview-content">
                                <h4 class="quickview-title">Surgical Face Mask</h4>
                                <div class="quickview-rating">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star-half-alt"></i>
                                    <i class="far fa-star"></i>
                                    <span class="rating-count"> (4 Customer Reviews)</span>
                                </div>
                                <div class="quickview-price">
                                    <h5><del>$860</del><span>$740</span></h5>
                                </div>
                                <ul class="quickview-list">
                                    <li>Brand:<span>Medica</span></li>
                                    <li>Category:<span>Healthcare</span></li>
                                    <li>Stock:<span class="stock">Available</span></li>
                                    <li>Code:<span>789FGDF</span></li>
                                </ul>
                                <div class="quickview-cart">
                                    <a href="#" class="theme-btn">Add to cart</a>
                                </div>
                                <div class="quickview-social">
                                    <span>Share:</span>
                                    <a href="#"><i class="fab fa-facebook-f"></i></a>
                                    <a href="#"><i class="fab fa-x-twitter"></i></a>
                                    <a href="#"><i class="fab fa-pinterest-p"></i></a>
                                    <a href="#"><i class="fab fa-instagram"></i></a>
                                    <a href="#"><i class="fab fa-linkedin-in"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- modal quick shop end -->
@endsection