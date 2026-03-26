<div class="row g-4">
    @forelse($products as $product)
        <div class="col-md-6 col-lg-4">
            <div class="product-item">
                <div class="product-img">
                    @if($product->discountPrice)
                        <span class="type discount">-{{round((($product->price - $product->discountPrice) / $product->price) * 100)}}%</span>
                    @endif
                    <a href="/shop-single/{{ $product->ean ?? $product->minsan }}">
                        <img src="{{asset('/storage-admin/' . $product->image) }}" alt="">
                    </a>
                </div>

                <div class="product-content">
                    <h3 class="product-title">
                        <a href="/shop-single/{{ $product->ean ?? $product->minsan }}">
                            {{ $product->name }}
                        </a>
                    </h3>

                    <div class="product-bottom mt-10">
                        <div class="col-6">
                            @if($product->stock > 0)
                                <span>Disponibile</span>
                            @else
                                <span>Non Disponibile</span>
                            @endif
                        </div>
                        <div class="col-6 product-price align-right">
                            @if(!$product->discountPrice)
                                <span>€{{ number_format($product->price, 2, '.', '') }}</span>
                            @else
                                <small class="strike">
                                    €{{ number_format($product->price, 2, '.', '') }}
                                </small>
                                <span>
                                    €{{ number_format($product->discountPrice, 2, '.', '') }}
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="shop-single-action">
                        <div class="row align-items-center text-center">
                            <div class="col-md-12">
                                <div class="shop-single-btn">
                                    <a href="/shop-single/{{ $product->ean ?? $product->minsan }}" class="theme-btn">Vai al Prodotto</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <p>Nessun prodotto trovato</p>
        </div>
    @endforelse
</div>

{{ $products->links() }}