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

            <g:description><![CDATA[{{ $product->merchant_description }}]]></g:description>

            <g:link>{{ url('/shop-single/' . (!empty($product->minsan) ? $product->minsan : $product->ean)) }}</g:link>

            @php
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];

                $image = $product->image;
                $extension = $image
                    ? strtolower(pathinfo($image, PATHINFO_EXTENSION))
                    : null;

                $validImage =
                    !empty($image) &&
                    in_array($extension, $allowedExtensions) &&
                    file_exists(public_path('storage-admin/' . $image));

                $imageUrl = $validImage
                    ? asset('/storage-admin/' . $image)
                    : asset('/storage-admin/products/file-non-disponibile.jpg');
            @endphp

            <g:image_link>{{ $imageUrl }}</g:image_link>

            <g:availability>
                {{ $product->stock > 0 ? 'in_stock' : 'out_of_stock' }}
            </g:availability>

            <g:price>{{ number_format($product->price, 2, '.', '') }} EUR</g:price>

            <g:condition>new</g:condition>

            @if($product->brandRelation)
                <g:brand><![CDATA[{{ $product->brandRelation->name }}]]></g:brand>
            @endif

            {{-- GTIN solo quando abbiamo realmente un EAN --}}
            @if($product->ean)
                <g:gtin>{{ $product->ean }}</g:gtin>
            @endif

        </item>

    @endforeach

</channel>
</rss>