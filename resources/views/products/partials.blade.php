<div class="row g-4">
    @forelse($products as $product)
        @php
            $discount = 0;
            if ($product->price > 0 && $product->discountPrice) {
                $discount = round((($product->price - $product->discountPrice) / $product->price) * 100);
            }

            $variantButtonLabel = 'Vai al Prodotto';
            if ($product->product_group_id && $product->productGroup) {
                // Considera tutte le varianti del gruppo, non solo il prodotto rappresentativo.
                $groupVariants = $product->productGroup->products;
                $attributes = [];
                if ($groupVariants->contains(fn ($variant) => filled($variant->variant_format))) {
                    $attributes[] = 'formato';
                }
                if ($groupVariants->contains(fn ($variant) => filled($variant->variant_color))) {
                    $attributes[] = 'colore';
                }
                if ($groupVariants->contains(fn ($variant) => filled($variant->variant_size))) {
                    $attributes[] = 'taglia';
                }
                if (count($attributes) === 1) {
                    $variantButtonLabel = 'Scegli ' . $attributes[0];
                } elseif (count($attributes) === 2) {
                    $variantButtonLabel = 'Scegli ' . implode(' e ', $attributes);
                } elseif (count($attributes) === 3) {
                    $variantButtonLabel = 'Scegli formato, colore e taglia';
                } else {
                    $variantButtonLabel = 'Scegli variante';
                }
            }
            $productUrl = '/shop-single/' . (!empty($product->minsan) ? $product->minsan : $product->ean);
            // Disponibilità: controlla tutte le varianti se è un gruppo
            $isAvailable = $product->product_group_id && $product->productGroup
                ? $product->productGroup->products->contains(
                    fn ($variant) => (int) $variant->stock > 0
                )
                : (int) $product->stock > 0;
        @endphp
        <div class="col-md-6 col-lg-4">
            <div class="product-item">
                <div class="product-img">
                    @if($discount > 0)
                        <span class="type discount">-{{ $discount }}%</span>
                    @endif
                    <a href="{{ $productUrl }}">
                        <img
                            src="{{ asset('/storage-admin/' . ($product->productGroup?->image ?: $product->image)) }}"
                            alt="{{ $product->productGroup?->name ?? $product->name }}"
                            onerror="this.onerror=null;this.src='{{ asset('/storage-admin/products/file-non-disponibile.jpg') }}';"
                        >
                    </a>
                </div>

                <div class="product-content">
                    <h3 class="product-title">
                        <a href="{{ $productUrl }}">
                            {{ $product->productGroup?->name ?? $product->name }}
                        </a>
                    </h3>

                    <div class="product-bottom mt-10">
                        <div class="col-6">
                            @if($isAvailable)
                                <span>Disponibile</span>
                            @else
                                <span>Non Disponibile</span>
                            @endif
                        </div>
                        <div class="col-6 product-price align-right">
                            @if(!$product->discountPrice || $product->price == $product->discountPrice)
                                <span>€{{ number_format($product->price, 2, '.', '') }}</span>
                            @else
                                <small class="strike">€{{ number_format($product->price, 2, '.', '') }}</small>
                                <span>€{{ number_format($product->discountPrice, 2, '.', '') }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="shop-single-action">
                        <div class="row align-items-center text-center">
                            <div class="col-md-12">
                                <div class="shop-single-btn">
                                    <a href="{{ $productUrl }}" class="theme-btn">{{ $variantButtonLabel }}</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12"><p>Nessun prodotto trovato</p></div>
    @endforelse
</div>


{{-- Desktop --}}
<div class="d-none d-md-flex justify-content-center mt-30">
    {{ $products->links() }}
</div>

{{-- Mobile --}}
<div class="d-md-none mt-30">
    <nav aria-label="Paginazione prodotti">
        <ul class="pagination justify-content-center">

            {{-- Freccia precedente --}}
            @if ($products->onFirstPage())
                <li class="page-item disabled">
                    <span class="page-link">‹</span>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link"
                       href="{{ $products->previousPageUrl() }}">‹</a>
                </li>
            @endif

            {{-- Pagina precedente --}}
            @if ($products->currentPage() > 1)
                <li class="page-item">
                    <a class="page-link"
                       href="{{ $products->url($products->currentPage() - 1) }}">
                        {{ $products->currentPage() - 1 }}
                    </a>
                </li>
            @endif

            {{-- Pagina corrente --}}
            <li class="page-item active" aria-current="page">
                <span class="page-link">
                    {{ $products->currentPage() }}
                </span>
            </li>

            {{-- Pagina successiva --}}
            @if ($products->hasMorePages())
                <li class="page-item">
                    <a class="page-link"
                       href="{{ $products->nextPageUrl() }}">
                        {{ $products->currentPage() + 1 }}
                    </a>
                </li>
            @endif

            {{-- Freccia successiva --}}
            @if ($products->hasMorePages())
                <li class="page-item">
                    <a class="page-link"
                       href="{{ $products->nextPageUrl() }}">›</a>
                </li>
            @else
                <li class="page-item disabled">
                    <span class="page-link">›</span>
                </li>
            @endif

        </ul>
    </nav>
</div>
