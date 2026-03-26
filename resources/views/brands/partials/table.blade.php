@foreach($brands as $letter => $group)
    <div class="letter-section">
        <h4 class="letter-title">{{ $letter }}</h4>

        <div class="brand-grid">
            @foreach($group as $brand)
                <a href="/shop-search?brand={{$brand->id}}" class="brand-item">
                    {{ strtoupper($brand->name) }}
                </a>
            @endforeach
        </div>
    </div>
@endforeach