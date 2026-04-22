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
                                    <form action="#">
                                        <div class="form-group">
                                            <input type="text" class="form-control" id="search" placeholder="Cerca">
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <div class="shop-widget">
                                <h4 class="shop-widget-title">Sotto Categorie</h4>
                                <ul class="shop-category-list">
                                    <li><a href="{{ request()->fullUrlWithQuery(['sub_category' => 0]) }}" class="{{ request('sub_category') == 0 ? 'active' : '' }}">Tutte<span>({{$category->products()->count()}})</span></a></li>
                                    @foreach($subcategory as $sub)
                                        <li>
                                            <a href="{{ request()->fullUrlWithQuery(['sub_category' => $sub->id]) }}"
                                               class="{{ request('sub_category') == $sub->id ? 'active' : '' }}">
                                                {{ $sub->name }}
                                                <span>({{ $sub->products_number() }})</span>
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                            <div class="shop-widget">
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
                            <button id="reset-filters" class="btn btn-md btn-outline-secondary" style="width:100%">Azzera filtri</button>

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
                    <div class="col-lg-9">
                        <div class="shop-item-wrap item-4">
                            <div class="row g-4">
                                <div id="products-wrapper">
                                    @include('products.partials', ['products' => $products])
                                </div>
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
            let subCategory = new URLSearchParams(window.location.search).get('sub_category');
            let brands = [];
            $('.brand-checkbox:checked').each(function () {
                brands.push($(this).val());
            });

            // Costruisci query string
            let params = new URLSearchParams();
            if (search) params.set('search', search);
            if (subCategory) params.set('sub_category', subCategory);
            if (brands.length > 0) brands.forEach(b => params.append('brands[]', b));

            // Aggiorna la barra dell’indirizzo
            let newUrl = window.location.pathname + '?' + params.toString();
            history.replaceState(null, '', newUrl);

            // Richiesta AJAX
            $.get(window.location.pathname, params.toString(), function (data) {
                $('#products-wrapper').html($(data).find('#products-wrapper').html());
                $('html, body').animate({
                    scrollTop: 0
                }, 300);
            });
        }

        // Ricerca al keyup
        $('#search').on('keyup', function () {
            updateProducts();
        });

        // Click su sotto-categorie
        $('.sidebar a').on('click', function (e) {
            e.preventDefault();
            let urlParams = new URLSearchParams(new URL($(this).attr('href')).search);
            let subCategory = urlParams.get('sub_category');
            // Aggiorna sub_category
            history.replaceState(null, '', window.location.pathname + '?sub_category=' + subCategory);
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


    </script>
@endsection