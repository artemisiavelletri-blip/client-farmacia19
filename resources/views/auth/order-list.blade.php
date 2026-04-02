@extends('master')

@section('content')

    <main class="main">

        
        <!-- user dashboard -->
        <div class="user-area bg pt-100 pb-80">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="user-wrapper">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="user-card">
                                        <div class="user-card-header">
                                            <h4 class="user-card-title">I Miei Ordini</h4>
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table table-borderless text-nowrap">
                                                <thead>
                                                    <tr>
                                                        <th></th>
                                                        <th>Numero ordine</th>
                                                        <th>Data di acquisto</th>
                                                        <th>Totale</th>
                                                        <th>Stato</th>
                                                        <th>Azione</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($orders as $order)
                                                        <tr>
                                                            <td>                                                               
                                                                <div class="table-list-img table-list-code text-center">
                                                                    <img src="{{asset('/storage-admin/' . $order->items()->first()->product()->first()->image) }}" width="80px">
                                                                </div>
                                                            </td>
                                                            <td><span class="table-list-code">#{{$order->order_number}}</span></td>
                                                            <td>{{$order->created_at->translatedFormat('d F Y')}}</td>
                                                            <td>€{{$order->total}}</td>
                                                            <td>
                                                                @if($order->status == 'pending')
                                                                    <span class="badge badge-info">Ordinato</span>
                                                                @elseif($order->status == 'processing')
                                                                    <span class="badge badge-primary">In Lavorazione</span>
                                                                @elseif($order->status == 'shipped')
                                                                    <span class="badge badge-primary">Spedito</span>
                                                                @elseif($order->status == 'delivered')
                                                                    <span class="badge badge-success">Consegnato</span>
                                                                @else
                                                                    <span class="badge badge-danger">Annullato</span>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                <a href="/order-detail/{{$order->order_number}}" class="btn btn-outline-secondary btn-sm rounded-2"><i class="far fa-eye"></i></a>
                                                            </td>
                                                        </tr>
                                                    @endforeach                                                    
                                                </tbody>
                                            </table>
                                            <div class="mt-3">
                                                {{ $orders->links() }}
                                            </div>
                                        </div>
                                        <div class="orders-mobile">
                                            @foreach($orders as $order)
                                                @php
                                                    $product = $order->items()->first()->product()->first();
                                                @endphp
                                                <div class="order-card">
                                                    <img src="{{ asset('/storage-admin/' . $product->image) }}" alt="{{ $product->name }}">
                                                    <div class="order-card-details">
                                                        <h5>Ordine #{{ $order->order_number }}</h5>
                                                        <div class="info">Data: {{ $order->created_at->translatedFormat('d F Y') }}</div>
                                                        <div class="info">Totale: €{{ $order->total }}</div>
                                                        <div class="info">
                                                            @if($order->status == 'pending')
                                                                <span class="badge badge-info">Ordinato</span>
                                                            @elseif($order->status == 'processing')
                                                                <span class="badge badge-primary">In Lavorazione</span>
                                                            @elseif($order->status == 'shipped')
                                                                <span class="badge badge-primary">Spedito</span>
                                                            @elseif($order->status == 'delivered')
                                                                <span class="badge badge-success">Consegnato</span>
                                                            @else
                                                                <span class="badge badge-danger">Annullato</span>
                                                            @endif
                                                        </div>
                                                        <a href="/order-detail/{{ $order->order_number }}" class="btn btn-outline-secondary btn-sm rounded-2 mt-1">
                                                            <i class="far fa-eye"></i> Dettagli
                                                        </a>
                                                    </div>
                                                </div>
                                            @endforeach

                                            {{-- Paginazione --}}
                                            <div class="mt-3">
                                                {{ $orders->links() }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- user dashboard end -->

    </main>

@endsection