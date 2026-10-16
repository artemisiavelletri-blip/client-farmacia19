@extends('master')

@php
    $isGrouped = !empty($product->product_group_id) && isset($variants) && $variants->isNotEmpty();
    $groupImagePath = $isGrouped ? ($product->productGroup?->image ?: $product->image) : $product->image;
    $variantData = $isGrouped ? $variants->map(fn ($v) => [
        'id' => $v->id, 'color' => $v->variant_color, 'size' => $v->variant_size, 'format' => $v->variant_format,
        'price' => (float) $v->price, 'discountPrice' => (float) $v->discountPrice,
        'stock' => (int) $v->stock, 'ean' => $v->ean, 'minsan' => $v->minsan,
        'image' => asset('/storage-admin/' . $v->image),
        'description' => $v->description,
    ])->values()->all() : [];
@endphp

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
                            <img id="variant-main-image" src="{{ asset('/storage-admin/' . $groupImagePath) }}" alt="#" onerror="this.onerror=null;this.src='{{ addslashes(asset('/storage-admin/products/file-non-disponibile.jpg')) }}';">
                        </div>
                    </div>
                    <div id="prod-info" class="col-md-12 col-lg-6 col-xxl-6">
                        <div class="shop-single-info">
                            <h4 class="shop-single-title">{{ $isGrouped ? ($product->productGroup?->name ?? $product->name) : $product->name }}</h4>
                            @if($isGrouped)
                                <div class="variant-picker my-3">
                                    <div id="variant-format-row" class="mb-3" hidden>
                                        <div class="variant-picker-heading">Formato <strong id="selected-format-label">Seleziona</strong></div>
                                        <div id="variant-format-options" class="variant-choice-list" role="group" aria-label="Scegli il formato"></div>
                                    </div>
                                    <div id="variant-color-row" class="mb-3" hidden>
                                        <div class="variant-picker-heading">Colore <strong id="selected-color-label">Seleziona</strong></div>
                                        <div id="variant-color-options" class="variant-choice-list" role="group" aria-label="Scegli il colore"></div>
                                    </div>
                                    <div id="variant-size-row" class="mb-2" hidden>
                                        <div class="variant-picker-heading">Taglia <strong id="selected-size-label">Seleziona</strong></div>
                                        <div id="variant-size-options" class="variant-choice-list" role="group" aria-label="Scegli la taglia"></div>
                                    </div>
                                    <small id="variant-feedback" aria-live="polite">Seleziona una variante.</small>
                                </div>
                            @endif
                            <div class="shop-single-cs">                                
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="shop-single-size">
                                            <h6>Quantità</h6>
                                            <div class="shop-cart-qty" style="display: inline;">
                                                <button type="button" class="minus-btn" {{ $isGrouped || $product->stock <= 0 ? 'disabled' : '' }}><i class="fal fa-minus"></i></button>
                                                <input class="quantity" id="quantity" type="number" min="1" step="1" value="1" max="{{ $isGrouped ? 0 : max(0, (int) $product->stock) }}" {{ $isGrouped || $product->stock <= 0 ? 'disabled' : '' }}>
                                                <button type="button" class="plus-btn" {{ $isGrouped || $product->stock <= 0 ? 'disabled' : '' }}><i class="fal fa-plus"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="shop-single-sortinfo">
                                <ul>
                                    <li>Stock: <span id="variant-stock">
                                        @if($product->stock > 0)
                                            Disponibile
                                        @else
                                            Non Disponibile
                                        @endif
                                    </span></li>
                                    <li>Ean: <span id="variant-ean">{{$product->ean}}</span></li>
                                    <li>Minsan: <span id="variant-minsan">{{$product->minsan}}</span></li>
                                    <li>Brand: <a href="/shop-search?brand={{$product->brand()->id}}">{{$product->brand()->name}}</a></li>
                                    @php $limit = 6; @endphp

                                    <div class="product-tags">
                                        <li>Tags: 
                                        {{-- primi --}}
                                        @foreach($tags->take($limit) as $tag)
                                            <a href="/shop-search?tag={{ $tag->slug }}">
                                                {{ $tag->name }}
                                            </a>@if(!$loop->last) · @endif
                                        @endforeach

                                        {{-- altri --}}
                                        <span class="tags-more d-none">
                                            @foreach($tags->slice($limit) as $tag)
                                                · <a href="/shop-search?tag={{ $tag->slug }}">
                                                    {{ $tag->name }}
                                                </a>
                                            @endforeach
                                        </span>

                                        @if($tags->count() > $limit)
                                            <button class="tags-toggle" type="button">
                                                +{{ $tags->count() - $limit }} altri
                                            </button>
                                        @endif
                                        </li>
                                    </div>
                                </ul>
                            </div>
                            <div class="row">
                                <div id="variant-price" class="col-md-12 d-flex align-items-center gap-2">
                                    @if(!$product->discountPrice)
                                        <h3 class="mb-0">€{{number_format((float)$product->price, 2, '.', '')}}</h3>
                                    @elseif($product->price == $product->discountPrice)
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
                                        <div class="shop-single-btn" id="variant-cart-action">
                                            @if($isGrouped)
                                                <span class="theme-btn theme-btn-disabled">Seleziona una variante</span>
                                            @elseif($product->stock > 0)
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
                                <div id="variant-description">{!! html_entity_decode($isGrouped ? ($product->productGroup?->description ?: $product->description) : $product->description) !!}</div>
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
    <style>
        /* Il valore della quantità rimane leggibile anche a due o più cifre. */
        .shop-single .shop-cart-qty #quantity {
            box-sizing: border-box;
            width: 76px !important;
            min-width: 76px !important;
            max-width: none;
            padding: 0 5px !important;
            text-align: center;
            font-variant-numeric: tabular-nums;
            flex: 0 0 76px;
            -moz-appearance: textfield;
        }
        .shop-single .shop-cart-qty #quantity::-webkit-inner-spin-button,
        .shop-single .shop-cart-qty #quantity::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
    </style>
    @if($isGrouped)
    <style>
        .variant-picker-heading {display:flex;align-items:center;gap:10px;margin-bottom:11px;color:#667085;font-size:14px}
        .variant-picker-heading strong {font-size:13px;color:#172c3e;font-weight:600}
        .variant-choice-list {display:flex;flex-wrap:wrap;gap:10px;min-height:43px;align-items:center}
        .variant-choice {width:46px;height:46px;border:1.5px solid #d6dce3;border-radius:50%;background:#fff;color:#243747;font-weight:600;font-size:13px;display:inline-flex;align-items:center;justify-content:center;cursor:pointer;transition:border-color .15s,box-shadow .15s,transform .15s;position:relative}
        .variant-choice:hover:not(:disabled) {border-color:#12649b;transform:translateY(-1px)}
        .variant-choice:focus-visible {outline:3px solid #89c6ec;outline-offset:3px}
        .variant-choice.is-selected {border-color:#075d93;box-shadow:0 0 0 3px rgba(7,93,147,.15)}
        .variant-choice.is-unavailable {opacity:.4;text-decoration:line-through}
        .variant-choice:disabled {cursor:not-allowed;opacity:.4}
        .variant-choice-format {width:auto;min-width:64px;padding:0 14px;border-radius:25px}
        .variant-choice-color {width:44px;height:44px;padding:4px}
        .variant-color-dot {width:100%;height:100%;border-radius:50%;border:1px solid rgba(0,0,0,.12);background:var(--variant-color,#e5e7eb)}
        .variant-choice-color.is-selected::after {content:'✓';position:absolute;right:-5px;bottom:-5px;width:19px;height:19px;border-radius:50%;background:#075d93;color:white;font-size:12px;line-height:19px;text-align:center}
        .variant-choice-color.is-text-color {width:auto;min-width:54px;padding:0 12px;border-radius:25px}
        .variant-choice-color.is-text-color.is-selected::after {right:-5px}
        @media(max-width:575px){.variant-choice{width:43px;height:43px}.variant-choice-color{width:43px;height:43px}}
    </style>
    @endif

    @if($isGrouped)
    <script type="application/json" id="product-variants-json">@json($variantData)</script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const jsonNode = document.getElementById('product-variants-json');
        let variants = JSON.parse(jsonNode.textContent);
        const fallback = @json(asset('/storage-admin/products/file-non-disponibile.jpg'));
        const groupImage = @json(asset('/storage-admin/' . $groupImagePath));
        const groupDescription = @json($product->productGroup?->description ?: $product->description);
        const descriptionElement = document.getElementById('variant-description');
        const feedback = document.getElementById('variant-feedback');
        const action = document.getElementById('variant-cart-action');
        const quantity = document.getElementById('quantity');
        const dimensions = ['format', 'color', 'size'];
        const labels = {format:'formato', color:'colore', size:'taglia'};
        const selected = {format:'', color:'', size:''};
        const controls = Object.fromEntries(dimensions.map(key => [key, {
            row:document.getElementById('variant-' + key + '-row'),
            options:document.getElementById('variant-' + key + '-options'),
            label:document.getElementById('selected-' + key + '-label')
        }]));
        const colorHex = {
            'BIANCO':'#ffffff','NERO':'#202124','BLU':'#245b9e','NAVY':'#172f52',
            'BLU NAVY':'#172f52','ROSSO':'#d63343','ROSA':'#efb1c7','GRIGIO':'#a1a6ad',
            'BEIGE':'#d8c5a3','MARRONE':'#78523c','VERDE':'#38805b','GIALLO':'#f2d34f',
            'ARANCIONE':'#ee9141','VIOLA':'#8055a3','BORDEAUX':'#773047','CELESTE':'#8dc8e8',
            'FUCSIA': '#CC5B87', 'SMERALDO': '#4FB9AB', 'EMERALD': '#227F78', 'LILLA': '#B299D0',
            'LIGHT BLUE': '#8098C0', 'VINACCIA': '#A9324E'
        };
        const normalize = value => value == null ? '' : String(value).trim();
        const unique = values => [...new Set(values.map(normalize).filter(Boolean))]
            .sort((a,b) => a.localeCompare(b,'it',{numeric:true}));
        const activeDimensions = () => dimensions.filter(key => variants.some(v => normalize(v[key])));
        const formatPrice = value => '€' + Number(value).toFixed(2).replace('.', ',');
        function setDescription(html) {
            if (descriptionElement) descriptionElement.innerHTML = html || groupDescription || '';
        }
        function setMainImage(url) {
            const img = document.getElementById('variant-main-image');
            img.onerror = function () { this.onerror = null; this.src = fallback; };
            img.src = url || fallback;
        }
        function toggleQuantity(enabled) {
            quantity.disabled = !enabled;
            const container = quantity.closest('.shop-cart-qty');
            if (!container) return;
            container.querySelectorAll('.minus-btn, .plus-btn').forEach(button => {
                button.disabled = !enabled;
                button.style.pointerEvents = enabled ? '' : 'none';
                button.style.opacity = enabled ? '' : '0.4';
                button.setAttribute('aria-disabled', enabled ? 'false' : 'true');
            });
        }
        function matchingVariant() {
            const active = activeDimensions();
            if (!active.every(key => selected[key])) return null;
            return variants.find(v => active.every(key => normalize(v[key]) === selected[key])) || null;
        }
        function renderColor(btn, value) {
            btn.classList.add('variant-choice-color');
            const parts = value.toUpperCase().split(/\s*[\/+]\s*/).map(x => x.trim()).filter(Boolean);
            const hexes = parts.map(part => colorHex[part]);
            if (!parts.length || !hexes.every(Boolean)) {
                btn.classList.add('is-text-color'); btn.textContent = value; return;
            }
            const dot = document.createElement('span'); dot.className = 'variant-color-dot';
            if (hexes.length === 1) dot.style.setProperty('--variant-color', hexes[0]);
            else if (hexes.length === 2){
                dot.style.background = `linear-gradient(90deg, ${hexes[0]} 0% 50%, ${hexes[1]} 50% 100%)`;
                dot.style.border = `none`;
            }
            else {
                const step = 100 / hexes.length;
                dot.style.background = 'conic-gradient(from -90deg, ' + hexes.map((hex,i) =>
                    `${hex} ${(i*step).toFixed(3)}% ${((i+1)*step).toFixed(3)}%`).join(', ') + ')';
            }
            btn.append(dot);
        }
        function renderChoices() {
            const active = activeDimensions();
            dimensions.forEach(key => {
                const control = controls[key];
                control.row.hidden = !active.includes(key);
                control.options.replaceChildren();
                control.label.textContent = selected[key] || 'Seleziona';
                if (!active.includes(key)) { selected[key] = ''; return; }
                const previous = active.slice(0, active.indexOf(key));
                const eligible = variants.filter(v => previous.every(p => normalize(v[p]) === selected[p]));
                if (previous.some(p => !selected[p])) {
                    const hint = document.createElement('small'); hint.className = 'text-muted';
                    hint.textContent = 'Scegli prima ' + labels[previous.find(p => !selected[p])];
                    control.options.append(hint); return;
                }
                unique(eligible.map(v => v[key])).forEach(value => {
                    const available = eligible.some(v => normalize(v[key]) === value && Number(v.stock) > 0);
                    const btn = document.createElement('button'); btn.type = 'button';
                    btn.className = 'variant-choice' + (key === 'format' ? ' variant-choice-format' : '');
                    btn.title = labels[key] + ' ' + value + (available ? '' : ' (esaurito)');
                    btn.setAttribute('aria-label', btn.title);
                    btn.setAttribute('aria-pressed', String(selected[key] === value));
                    if (selected[key] === value) btn.classList.add('is-selected');
                    if (!available) btn.classList.add('is-unavailable');
                    if (key === 'color') renderColor(btn,value); else btn.textContent = value;
                    btn.addEventListener('click', function () {
                        const index = active.indexOf(key);
                        selected[key] = selected[key] === value ? '' : value;
                        active.slice(index + 1).forEach(later => selected[later] = '');
                        updateDetails(); renderChoices();
                    });
                    control.options.append(btn);
                });
            });
        }
        function resetDetails() {
            setDescription(groupDescription);
            action.innerHTML = '<span class="theme-btn theme-btn-disabled">Seleziona una variante</span>';
            document.getElementById('variant-stock').textContent = 'Seleziona una variante';
            document.getElementById('variant-ean').textContent = '—';
            document.getElementById('variant-minsan').textContent = '—';
            document.getElementById('variant-price').textContent = 'Seleziona una variante per vedere il prezzo';
            quantity.value = 1; quantity.max = 0; toggleQuantity(false);
        }
        function updateDetails() {
            const v = matchingVariant();
            if (!v) {
                resetDetails();
                const active = activeDimensions();
                const prefix = active.filter(key => selected[key]);
                const first = variants.find(item => prefix.every(key => normalize(item[key]) === selected[key]));
                setMainImage(first && prefix.length ? first.image : groupImage);
                const remaining = active.filter(key => !selected[key]);
                feedback.textContent = remaining.length ? 'Seleziona ' + remaining.map(k => labels[k]).join(', ') : 'Variante non disponibile';
                return;
            }
            document.getElementById('variant-stock').textContent = Number(v.stock) > 0 ? 'Disponibile' : 'Non disponibile';
            document.getElementById('variant-ean').textContent = v.ean || '—';
            document.getElementById('variant-minsan').textContent = v.minsan || '—';
            setMainImage(v.image);
            setDescription(v.description || groupDescription);
            const price = document.getElementById('variant-price'); price.replaceChildren();
            if (Number(v.discountPrice) > 0 && Number(v.discountPrice) < Number(v.price)) {
                const old = document.createElement('small'); old.className = 'strike mb-0';
                old.textContent = formatPrice(v.price); price.append(old);
            }
            const current = document.createElement('h3'); current.className = 'mb-0';
            current.textContent = formatPrice(Number(v.discountPrice) > 0 && Number(v.discountPrice) < Number(v.price) ? v.discountPrice : v.price);
            price.append(current);
            quantity.value = 1; quantity.max = Math.max(0,Number(v.stock));
            toggleQuantity(Number(v.stock) > 0);
            action.replaceChildren();
            const btn = document.createElement('span');
            if (Number(v.stock) > 0) {
                btn.className = 'add-to-cart theme-btn'; btn.dataset.id = v.id;
                btn.innerHTML = '<span class="far fa-shopping-bag"></span>Aggiungi al Carrello';
                feedback.textContent = 'Variante disponibile';
            } else {
                btn.className = 'theme-btn theme-btn-disabled';
                btn.textContent = 'Prodotto Non Disponibile';
                feedback.textContent = 'Questa variante non è disponibile';
            }
            action.append(btn);
        }
        let stockRefreshPending = false;
        $(document).on('cart:updated product:refresh-stock', function () {
            if (stockRefreshPending) return;
            stockRefreshPending = true;
            $.ajax({url:window.location.pathname + window.location.search,method:'GET',cache:false,dataType:'html'})
                .done(function (html) {
                    const page = new DOMParser().parseFromString(html,'text/html');
                    const node = page.getElementById('product-variants-json');
                    if (!node) return;
                    let updated;
                    try { updated = JSON.parse(node.textContent); }
                    catch(error) { console.error('Dati varianti non validi',error); return; }
                    if (!Array.isArray(updated)) return;
                    variants = updated;
                    const active = activeDimensions();
                    active.forEach((key,index) => {
                        const previous = active.slice(0,index);
                        const valid = variants.some(v => previous.every(p => normalize(v[p]) === selected[p]) && normalize(v[key]) === selected[key]);
                        if (!valid) { selected[key] = ''; active.slice(index+1).forEach(later => selected[later] = ''); }
                    });
                    updateDetails(); renderChoices();
                }).fail(function () { console.error('Impossibile aggiornare la disponibilità delle varianti.'); })
                .always(function () { stockRefreshPending = false; });
        });
        updateDetails(); renderChoices();
    });
    </script>
    @endif
    <script type="text/javascript">
        // Quantità: rispetta sempre la disponibilità della variante selezionata.
        // Gestione esclusiva della quantità nella scheda prodotto.
        // La fase capture evita che gli handler +/- del tema eseguano un secondo incremento.
        document.addEventListener('click', function (event) {
            const button = event.target.closest('.shop-single .shop-cart-qty .plus-btn, .shop-single .shop-cart-qty .minus-btn');
            if (!button) return;
            event.preventDefault();
            event.stopPropagation();
            event.stopImmediatePropagation();
            const field = document.getElementById('quantity');
            if (!field || field.disabled || button.disabled) return;
            const max = Math.max(0, parseInt(field.max, 10) || 0);
            if (max < 1) return;
            const current = Math.max(1, parseInt(field.value, 10) || 1);
            field.value = button.classList.contains('plus-btn') ? Math.min(max, current + 1) : Math.max(1, current - 1);
            field.dispatchEvent(new Event('change', { bubbles: true }));
        }, true);
        $(document).on('change blur', '#quantity', function () {
            const max = Math.max(0, parseInt(this.max, 10) || 0);
            this.value = max > 0 ? Math.min(max, Math.max(1, parseInt(this.value, 10) || 1)) : 1;
        });

        let cartRequestPending = false;
        $(document).on('click', '#variant-cart-action .add-to-cart', function (event) {
            event.preventDefault();
            if (cartRequestPending) return;
            const button = $(this);
            const productId = button.data('id');
            const field = document.getElementById('quantity');
            const qta = Number(field?.value);
            const max = Number(field?.max);
            if (!productId || !field || field.disabled || !Number.isInteger(qta) || qta < 1 || qta > max) return;
            cartRequestPending = true;
            button.css({ 'pointer-events': 'none', 'opacity': '0.5' }).attr('aria-disabled', 'true');
            $.ajax({
                url: '/cart/add',
                type: 'POST',
                data: {
                    product_id: productId,
                    quantity: qta,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) {
                    // Non considerare un rifiuto applicativo come aggiunta riuscita.
                    if (response && (response.success === false || response.error === true)) {
                        alert(response.message || 'Prodotto non disponibile nella quantità richiesta.');
                    }
                    // Ricarica per leggere lo stock aggiornato dal server.
                    // Non sottraiamo localmente la quantità: potrebbe essere già nel carrello.
                    window.location.reload();
                },
                error: function (xhr) {
                    if (xhr.status === 401) {
                        $.post('/store-intended-url', {
                            url: window.location.href,
                            _token: $('meta[name="csrf-token"]').attr('content')
                        }).done(function () { window.location.href = '/login'; });
                        return;
                    }
                    alert(xhr.responseJSON?.message || 'Impossibile aggiungere il prodotto. Verifica la disponibilità.');
                    if ([409, 422].includes(xhr.status)) window.location.reload();
                },
                complete: function () {
                    cartRequestPending = false;
                    button.css({ 'pointer-events': '', 'opacity': '' }).removeAttr('aria-disabled');
                }
            });
        });

        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.tags-toggle').forEach(button => {
                button.addEventListener('click', function () {
                    const container = this.closest('.product-tags');
                    const more = container.querySelector('.tags-more');

                    if (more.classList.contains('d-none')) {
                        more.classList.remove('d-none');
                        this.textContent = 'Mostra meno';
                    } else {
                        more.classList.add('d-none');
                        this.textContent = '+' + more.querySelectorAll('a').length + ' altri';
                    }
                });
            });
        });
    </script>
@endsection