@extends('master')

@section('content')

    <main class="main">

        <!-- shop-area -->
        <div class="shop-area bg py-90">
            <div class="container">
                <div class="row category-title">
                    <h3>
                        Coupon per te
                    </h3>
                </div>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="shop-item-wrap item-4">
                            <div class="row g-4">
                                <div id="products-wrapper">
                                    @include('coupon.partials',['promotion' => $promotion])
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