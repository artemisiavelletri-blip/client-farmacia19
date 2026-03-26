@extends('master')

@section('content')

    <main class="main">

        <!-- shop-area -->
        <div class="shop-area bg py-90">
            <div class="container">
                <div class="row category-title">
                    <h3>
                        @if($category)
                            {{$category . ' > ' . $search}}
                        @elseif($tag)
                            {{'Tag > ' . $tag}}
                        @elseif($type_message)
                            {{$type_message}}
                        @else
                            {{$search}}
                        @endif
                    </h3>
                </div>
                <div class="row">
                    <div class="col-lg-12">
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