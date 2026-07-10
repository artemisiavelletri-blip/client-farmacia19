@extends('master')

@section('content')

    <main class="main">

        <!-- shop-area -->
        <div class="shop-area bg py-90">
            <div class="container">
                <div class="row category-title">
                    <h3>{{$category->name}}</h3>
                </div>
                <div class="row">
                    <div class="col-lg-3">
                        <div class="shop-sidebar">
                            <div class="shop-widget">
                                <div class="shop-search-form">
                                    <h4 class="shop-widget-title">Cerca Prodotto</h4>
                                    <div class="form-group">
                                        <input type="text" class="form-control mobile-hidden" id="search" placeholder="Cerca" value="{{ $search ?? '' }}">
                                        <div class="search-content">
                                            <input type="text" class="form-control mobile-show" id="searchMobile" name="search" placeholder="Cerca">
                                            <button class="search-btn" id="search-btn-mobile"><i class="far fa-search" style="margin-top: 5px;"></i></button>
                                        </div>
                                    </div>
                                </div>
                            </div>                            
                            <div class="shop-widget filters mobile-hidden">
                                <h4 class="shop-widget-title">Sotto Categorie</h4>
                                <ul class="shop-category-list">
                                    <li>
                                        <a data-id="0">Tutte<span>({{$category->products()->count()}})</span></a>
                                    </li>
                                    @foreach($subcategory as $sub)
                                        <li>
                                            <a
                                                data-id="{{$sub->id}}">
                                                {{ $sub->name }}
                                                <span>({{ $sub->products_number() }})</span>
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                            <div class="shop-widget filters mobile-hidden">
                                <h4 class="shop-widget-title">Brands</h4>
                                <ul class="shop-checkbox-list">
                                    @foreach($category->brands() as $brand)
                                        <div class="form-check">
                                            <input class="form-check-input brand-checkbox"
                                                   type="checkbox"
                                                   value="{{ $brand->id }}"
                                                   id="brand{{ $brand->id }}"
                                                   {{ in_array($brand->id, request('brands', [])) ? 'checked' : '' }}>
                                            <label for="brand{{ $brand->id }}">
                                                {{ $brand->name }} ({{ $brand->products()->where('category_id', $category->id)->count() }})
                                            </label>
                                        </div>
                                    @endforeach                                 
                                </ul>
                            </div>
                            <button id="show-filters" class="btn btn-md btn-outline-secondary mb-15 mobile-show" style="width:100%">Mostra Filtri</button>
                            <button id="reset-filters" class="btn btn-md btn-outline-secondary" style="width:100%">Azzera filtri</button>

                            <div class="shop-widget-banner mt-30 mb-50 mobile-hidden">
                                <div class="banner-img" style="background-image:url({{asset('img/product/free_shipping.png')}})"></div>
                                <div class="banner-content">
                                    <h6>SPEDIZIONI <span>VELOCI</span></h6>
                                    <h4>1-2 Giorni</h4>
                                    <h6>SPEDIZIONI <span>GRATUITE</span></h6>
                                    <h4>Per ordini superiori ai €49,90</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-9">
                        <div class="shop-item-wrap item-4">
                            <div class="row g-4">
                                <div id="products-wrapper">
                                    @include('products.partials', ['products' => $products])
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 mobile-show">
                        <div class="shop-widget-banner mt-30 mb-50">
                            <div class="banner-img" style="background-image:url({{asset('img/product/free_shipping.png')}})"></div>
                            <div class="banner-content">
                                <h6>SPEDIZIONI <span>VELOCI</span></h6>
                                <h4>1-2 Giorni</h4>
                                <h6>SPEDIZIONI <span>GRATUITE</span></h6>
                                <h4>Per ordini superiori ai €49,90</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- shop-area end -->

    </main>

@endsection

@section('js')

    <script type="text/javascript">
       function updateProducts() {
            // Prendi valori filtri
            let search = $('#search').val();
            let searchMobile = $('#searchMobile').val();

            let subCategory = $('.shop-category-list a.active').data('id');

            let brands = [];
            $('.brand-checkbox:checked').each(function () {
                brands.push($(this).val());
            });


            console.log(subCategory);
            // Costruisci query string
            let params = new URLSearchParams();
            if (search) params.set('search', search);
            if (searchMobile) params.set('search', searchMobile);
            if (subCategory) params.set('sub_category', subCategory);
            if (brands.length > 0) brands.forEach(b => params.append('brands[]', b));

            // Aggiorna la barra dell’indirizzo
            let newUrl = window.location.pathname + '?' + params.toString();
            history.replaceState(null, '', newUrl);

            // Richiesta AJAX
            $.get(window.location.pathname, params.toString(), function (data) {
                $('#products-wrapper').html($(data).find('#products-wrapper').html());

                $('html, body').animate({
                    scrollTop: $('#products-wrapper').offset().top  - 250
                }, 300);
            });
        }

        // Ricerca al keyup
        $('#search').on('keyup', function () {
            updateProducts();
        });

        $('#search-btn-mobile').on('click', function () {
            updateProducts();
        });

        $('#searchMobile').on('keypress', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                $('#search-btn-mobile').click();
            }
        });

        // Click su sotto-categorie
        $('.shop-category-list a').on('click', function (e) {
            e.preventDefault();   
            $('.shop-category-list a').removeClass('active');
            $(this).addClass('active');      
            updateProducts();
        });

        // Cambiamento brand
        $('.brand-checkbox').on('change', function () {
            updateProducts();
        });

        $('#reset-filters').on('click', function () {
            // Pulisci input e checkbox
            $('#search').val('');
            $('.brand-checkbox').prop('checked', false);
            $('.shop-category-list a').removeClass('active');

            // Torna a sub_category = 0 (tutte)
            let params = new URLSearchParams();
            params.set('sub_category', 0);

            // Aggiorna barra indirizzo
            history.replaceState(null, '', window.location.pathname + '?' + params.toString());

            // Ricarica prodotti
            $.get(window.location.pathname, params.toString(), function (data) {
                $('#products-wrapper').html($(data).find('#products-wrapper').html());
                $('html, body').animate({
                    scrollTop: 0
                }, 300);
            });
        });

        $('#show-filters').on('click',function(){
            if($('.filters').hasClass('mobile-hidden')){
                $('.filters').removeClass('mobile-hidden');
                $('#show-filters').text('Nascondi Filtri');
            } else {
                $('.filters').addClass('mobile-hidden');
                $('#show-filters').text('Mostra Filtri');
            }
        })
    </script>
@endsection