{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}

<rss xmlns:g="http://base.google.com/ns/1.0" version="2.0">
<channel>

    <title>Farmacia19</title>
    <link>https://farmacia19.it/</link>
    <description>Catalogo prodotti Farmacia19</description>

    @foreach($products as $product)

        <item>

            {{-- MINSAN come identificativo del prodotto --}}
            <g:id>{{ $product->minsan }}</g:id>

            <g:title><![CDATA[
                {{ $product->name }}
            ]]></g:title>

            <g:description><![CDATA[
                {{ strip_tags($product->description ?? '') }}
            ]]></g:description>

            <g:link>{{ url('/shop-single/' . !empty($product->minsan) ? $product->minsan : $product->ean ) }}</g:link>

            <g:image_link>{{ asset('/storage-admin/' . $product->image) }}</g:image_link>

            <g:availability>
                {{ $product->stock > 0 ? 'in_stock' : 'out_of_stock' }}
            </g:availability>

            <g:price>{{ number_format($product->price, 2, '.', '') }} EUR</g:price>

            <g:condition>new</g:condition>

            @if($product->brand)
                <g:brand><![CDATA[
                    {{ $product->brand->name }}
                ]]></g:brand>
            @endif

            {{-- GTIN solo quando abbiamo realmente un EAN --}}
            @if($product->ean)
                <g:gtin>{{ $product->ean }}</g:gtin>
            @endif

        </item>

    @endforeach

</channel>
</rss>