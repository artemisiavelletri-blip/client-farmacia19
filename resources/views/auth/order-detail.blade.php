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
                                <div class="col-lg-12 mb-30 back-mobile">
                                    <a href="/order-list" class="theme-btn" style="width: 200px"><span class="fas fa-arrow-left"></span>Lista degli Ordini</a>
                                </div>
                                <div class="col-lg-12">
                                    <div class="user-card user-order-detail">
                                        @if(session('success'))
                                            <div class="alert alert-success">
                                                {{ session('success') }}
                                            </div>
                                        @endif

                                        @if(session('error'))
                                            <div class="alert alert-danger">
                                                {{ session('error') }}
                                            </div>
                                        @endif
                                        <div class="user-card-header user-card-header">
                                            <h4 class="user-card-title">Dettagli Ordine (#{{$order->order_number}})</h4>
                                            <div class="user-card-header-right">
                                                <a href="/order-list" class="theme-btn back-laptop"><span class="fas fa-arrow-left"></span>Lista degli Ordini</a>
                                                @if(($cancelled || $refund) && !$order->refunds()->exists())
                                                    <div class="dropdown-custom">
                                                        <button class="btn btn-sm dropdown-btn">⋮</button>

                                                        <div class="dropdown-menu-custom">
                                                            @if($cancelled)
                                                                <a href="/settings/order-delete/{{$order->order_number}}">Annulla Ordine</a><br>
                                                            @endif
                                                            @if($refund)
                                                                <a href="/refund-request/{{$order->order_number}}">Richiedi Rimborso</a>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endif
                                            </div> 
                                        </div>
                                        @if(!$order->returns->isEmpty())
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <div class="order-detail-content">
                                                        <h5>Stato Reso/Rimborso</h5>
                                                        <div class="order-progress"> 
                                                            @php
                                                                if ($order->refunds()->exists()) {
                                                                    $step_refund = 100;
                                                                } elseif ($order->returns->first()->status >= 1 && $order->returns->first()->status != 2) {
                                                                    $step_refund = 50;
                                                                } elseif ($order->returns->first()->status >= 0 && $order->returns->first()->status != 2) {
                                                                    $step_refund = 13;
                                                                } else {
                                                                    $step_refund = 0;
                                                                }
                                                            @endphp       
                                                            <div class="progress-bar" style="width: {{ $step_refund }}%; background-color: green;"></div>
                                                            <div class="step {{ ($order->returns->first()->status >= 0 && $order->returns->first()->status != 2) ? 'active' : '' }}">
                                                                <div class="icon">
                                                                    <i class="fa fa-plane" aria-hidden="true"></i>
                                                                </div>
                                                                <div class="label">Richiesta Rimborso</div>
                                                            </div>

                                                            <div class="step {{ ($order->returns->first()->status >= 1 && $order->returns->first()->status != 2) ? 'active' : '' }}">
                                                                <div class="icon">
                                                                    <i class="fa-solid fa-clock"></i>
                                                                </div>
                                                                <div class="label">In Lavorazione</div>
                                                            </div>

                                                            <div class="step {{ ($order->refunds()->exists() ? 'active' : '') }}">
                                                                <div class="icon">
                                                                    <i class="fa fa-eur" aria-hidden="true"></i>
                                                                </div>
                                                                <div class="label">Rimborso Emesso</div>
                                                            </div>
                                                        </div>
                                                        @if($order->returns->first()->status == 2)
                                                            <div class="alert alert-danger text-center">
                                                                <i class="bi bi-x-circle"></i>
                                                                Richiesta Rifiutata
                                                            </div>
                                                        @endif
                                                        @if($order->refunds()->exists())
                                                            <div class="alert alert-success text-center">
                                                                <i class="bi bi-x-circle"></i>
                                                                E' stato emesso un rimborso pari a € {{$order->refunds->amount}}
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                        <div class="table-responsive">
                                            <table class="table table-borderless text-nowrap">
                                                <thead>
                                                    <tr>
                                                        <th>Prodotto</th>
                                                        <th>Quantità</th>
                                                        <th>Prezzo</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($order->items as $item)
                                                        <tr>
                                                            <td>
                                                                <div class="table-list-info">
                                                                    <a href="/shop-single/{{ $item->product()->first()->ean ?? $item->product()->first()->minsan }}">
                                                                        <div class="table-list-img">
                                                                            <img src="{{asset('/storage-admin/' . $item->product()->first()->image) }}" alt="">
                                                                        </div>
                                                                        <div class="table-list-content">
                                                                            <h6>{{$item->product_name}}</h6>
                                                                        </div>
                                                                    </a>
                                                                </div>
                                                            </td>
                                                            <td>{{$item->quantity}}</td>
                                                            <td>€{{$item->price}}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-6">
                                                <div class="order-detail-content">
                                                    <h5>Informazioni Spedizione</h5>
                                                    <p>{{$order->recipient_name}}</p>
                                                    <p><i class="far fa-location-dot"></i> {{$order->address . ', ' . $order->city->name . ', ' . $order->city->region . ', ' . $order->city->province . ', ' .  $order->cap}}</p>
                                                    @if($order->note)
                                                        <p>{{$order->note}}</p>
                                                    @endif
                                                    <p><i class="fa-solid fa-phone"></i>{{$order->phone}}</p>
                                                </div>
                                            </div>
                                            <div class="col-lg-6">
                                                <div class="order-detail-content">
                                                    <h5>Riepilogo Ordine</h5>
                                                    <ul>
                                                        <li>Sub Totale<span>€{{$order->subtotal}}</span></li>
                                                        <li>Spese di Spedizione<span>€{{$order->shipping_cost}}</span></li>
                                                        @if($order->payment_method == 'cash_on_delivery')
                                                            <li>Contrassegno<span>€2.00</span></li>
                                                        @endif
                                                        <li>IVA<span>€{{$order->total_vat}}</span></li>
                                                        <li>Totale<span>€{{$order->total}}</span></li>
                                                    </ul>
                                                    <p class="mt-4">
                                                        @if($order->payment_method == "stripe")
                                                            Pagamento con carta
                                                        @elseif($order->payment_method == "paypal")
                                                            Pagamento con PayPal
                                                        @elseif($order->payment_method == "bank_transfer")
                                                            Pagamento con bonifico
                                                        @else
                                                            Pagamento con contrassegno
                                                        @endif
                                                    </p>
                                                </div>
                                            </div>
                                        </div>                                        
                                        <div class="row mt-30">
                                            <div class="col-lg-12">
                                                <div class="order-detail">
                                                    <h5>Stato dell'ordine</h5>
                                                    <div class="order-progress">
                                                        @php
                                                            $step = 0;
                                                            if ($order->status_step - 1 == 0) {
                                                                $step = 13;
                                                            } elseif ($order->status_step - 1 == 1) {
                                                                $step = 38;
                                                            } elseif ($order->status_step - 1 == 2) {
                                                                $step = 65;
                                                            } elseif ($order->status_step - 1 == 3) {
                                                                $step = 100;
                                                            }
                                                        @endphp
                                                        <div class="progress-bar" style="width: {{ $step }}%; background-color: green;"></div>
                                                        <div class="step {{ $order->status_step >= 1 ? 'active' : '' }}">
                                                            <div class="icon">
                                                                <i class="fa-solid fa-clock"></i>
                                                            </div>
                                                            <div class="label">In Attesa</div>
                                                        </div>

                                                        <div class="step {{ $order->status_step >= 2 ? 'active' : '' }}">
                                                            <div class="icon">
                                                                <i class="fa-solid fa-gear"></i>
                                                            </div>
                                                            <div class="label">In Preparazione</div>
                                                        </div>

                                                        <div class="step {{ $order->status_step >= 3 ? 'active' : '' }}">
                                                            <div class="icon">
                                                                <i class="fa-solid fa-truck"></i>
                                                            </div>
                                                            <div class="label">Spedito</div>
                                                        </div>

                                                        <div class="step {{ $order->status_step >= 4 ? 'active' : '' }}">
                                                            <div class="icon">
                                                                <i class="fa-solid fa-check"></i>
                                                            </div>
                                                            <div class="label">Consegnato</div>
                                                        </div>
                                                    </div>
                                                    @if($order->status === 'cancelled')
                                                        <div class="alert alert-danger text-center">
                                                            <i class="bi bi-x-circle"></i>
                                                            Ordine Annullato
                                                        </div>
                                                    @elseif($order->tracking_number && $trackingData)
                                                        <div class="alert alert-light">
                                                            @php
                                                                $shippingStatusesDelivery = [
                                                                    'INIT' => '- Sono state aggiunte nuove spedizioni, il cui tracciamento è ancora in corso',
                                                                    'NO_RECORD' => "- Per questa spedizione non sono ancora disponibili informazioni di tracciamento",
                                                                    'INFO_RECEIVED' => "- Il corriere ha ricevuto la richiesta dal mittente e si sta preparando a ritirare il pacco",
                                                                    'IN_TRANSIT' => "- La spedizione è in transito",
                                                                    'WAITING_DELIVERY' => "- La spedizione è in consegna oppure è arrivata al punto di ritiro",
                                                                    'DELIVERY_FAILED' => "- Il corriere ha tentato la consegna ma non è riuscito a causa di problemi con l'indirizzo, irreperibilità del destinatario, ecc.",
                                                                    'ABNORMAL' => "",
                                                                    'DELIVERED' => "- Pacco consegnato con successo",
                                                                    'EXPIRED' => ""
                                                                ];
                                                            @endphp
                                                            <h3>Stato della Spedizione {{$shippingStatusesDelivery[$status]}}</h3>
                                                            <ul>
                                                                
                                                                @foreach($trackingData as $event)
                                                                    <li class="mt-15">
                                                                        <strong>{{ date('d/m/Y H:i', strtotime($event['eventTime'])) }}</strong><br>
                                                                        Luogo: {{ $event['address'] ?? '-' }}<br>
                                                                        Dettagli: {{ $event['eventDetail'] ?? '-' }}
                                                                        @if(!$loop->last)
                                                                            <hr>
                                                                        @endif
                                                                    </li>
                                                                @endforeach
                                                            </ul>
                                                        </div>
                                                    @endif
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
        </div>
        <!-- user dashboard end -->

    </main>
@endsection

@section('js')
    
    <script>
        document.addEventListener("click", function (event) {

            // Se clicco sul bottone
            if (event.target.closest(".dropdown-btn")) {
                let dropdown = event.target.closest(".dropdown-custom");
                dropdown.querySelector(".dropdown-menu-custom")
                        .classList.toggle("show");
                return;
            }

            // Se clicco fuori → chiudi tutti i dropdown
            document.querySelectorAll(".dropdown-menu-custom").forEach(menu => {
                menu.classList.remove("show");
            });

        });
    </script>

@endsection