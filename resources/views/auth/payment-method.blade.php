@extends('master')
    <?php

    // Pagamento OneClik - Primo pagamento - Avvio pagamento

    // Alias e chiave segreta 
    $ALIAS = env('XPAY_ALIAS'); // Sostituire con il valore fornito da Nexi
    $CHIAVESEGRETA = env('XPAY_SECRET'); // Sostituire con il valore fornito da Nexi

    $requestUrl = env('NEXI_BASE_URL');
    //$merchantServerUrl = "http://" . $_SERVER['HTTP_HOST'] . "/cards/";
    $merchantServerUrl = "https://" . $_SERVER['HTTP_HOST'];

    $codTrans = "PS" . date('YmdHis');
    $divisa = "EUR";
    $importo = 0;

    // Calcolo MAC
    $mac = sha1('codTrans=' . $codTrans . 'divisa=' . $divisa . 'importo=' . $importo . $CHIAVESEGRETA);

    $numContratto = "NC_" . date('YmdHis');
    $tipoRichiesta = 'PP';

    // Parametri obbligatori
    $obbligatori = array(
        'alias' => $ALIAS,
        'importo' => $importo,
        'divisa' => $divisa,
        'codTrans' => $codTrans,
        'url' => $merchantServerUrl . "/payment-method/store",
        'url_back' => $merchantServerUrl . "/settings/payment-method",
        'mac' => $mac,
        'num_contratto' => $numContratto,
        'tipo_servizio' => 'paga_oc3d',
        'tipo_richiesta' => $tipoRichiesta,
        );

    \Illuminate\Support\Facades\Cache::put(
        'nexi_payment_' . $codTrans,
        [
            'user_id' => auth()->id(),
            'tipo_richiesta' => 'PP',
            'num_contratto' => $numContratto,
        ],
        now()->addMinutes(30)
    );

    // Parametri facoltativi
    $facoltativi = array(
    );

    $requestParams = array_merge($obbligatori, $facoltativi);

    ?>

@section('content')

    <main class="main">

        <!-- user dashboard -->
        <div class="user-area bg pt-100 pb-80">
            <div class="container">
                <div class="row">
                    <div class="col-lg-3">
                        @include('auth.settingsSideBar')
                    </div>
                    <div class="col-lg-9">
                        <div class="user-wrapper">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="user-card">
                                        <div class="user-card-header mobile-card-header">
                                            <h4 class="user-card-title">Metodo di Pagamento</h4>
                                            <div class="user-card-header-right">
                                                <form method='POST' action='<?php echo $requestUrl ?>'>
                                                    <?php foreach ($requestParams as $name => $value) { ?>
                                                        <input type='hidden' name='<?php echo $name; ?>' value='<?php echo htmlentities($value); ?>' />
                                                    <?php } ?>
                                                    
                                                    <button class="theme-btn"><span class="far fa-plus-circle f19-payment-desktop"></span>Aggiungi Metodo di Pagamento</button>
                                                </form>
                                            </div>
                                        </div>

                                        {{-- VERSIONE DESKTOP: tabella originale --}}
                                        <div class="f19-payment-desktop">
                                            <div class="mobile-table-custom">
                                                <table class="table table-borderless text-nowrap">
                                                    <thead>
                                                        <tr>
                                                            <th>Info Carta</th>
                                                            <th>Nome Carta</th>
                                                            <th>Numero Carta</th>
                                                            <th>Data Scadenza</th>
                                                            <th>Azione</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($payment_method as $card)
                                                            <tr>
                                                                <td>
                                                                    <div class="table-list-img w-25" style="height: auto!important;">
                                                                        @if($card->brand == 'visa' || $card->brand == 'VISA')
                                                                            <img class="rounded-3" src="{{ asset('/img/payment/visa.svg') }}" alt="">
                                                                        @elseif($card->brand == 'mastercard')
                                                                            <img class="rounded-3" src="{{ asset('/img/payment/mastercard.png') }}" alt="">
                                                                        @else
                                                                            <img class="rounded-3" src="{{ asset('/img/payment/mastercard.png') }}" alt="">
                                                                        @endif
                                                                    </div>
                                                                </td>
                                                                <td data-label="Nome Carta"><span class="table-list-code">{{$card->holder_name}}</span></td>
                                                                <td data-label="Numero Carta">***********{{$card->last4}}</td>
                                                                <td data-label="Data Scadenza">{{$card->exp_month}}/{{$card->exp_year}}</td>
                                                                <td data-label="Azione">
                                                                    <a href="#" class="btn btn-outline-danger btn-sm rounded-2 delete-card" data-tooltip="tooltip" title="Delete" data-id="{{$card->stripe_payment_method_id}}"><i class="far fa-trash-can"></i></a>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>

                                        {{-- VERSIONE MOBILE: nuove carte bancarie --}}
                                        <div class="f19-payment-mobile">
                                            <div class="f19-payment-grid">
                                                @forelse($payment_method as $card)
                                                    @php
                                                        $brand = strtolower($card->brand ?? '');
                                                        $isDefault = (bool) ($card->default ?? $card->is_default);
                                                        $cardClass = $brand === 'visa'
                                                            ? 'f19-card-visa'
                                                            : ($brand === 'mastercard'
                                                                ? 'f19-card-mastercard'
                                                                : 'f19-card-generic');
                                                    @endphp

                                                    <div class="f19-payment-item">

                                                        <div class="f19-bank-card {{ $cardClass }}">

                                                            <div class="f19-card-top">
                                                                <div>
                                                                    @if($isDefault)
                                                                        <span class="f19-default-badge">
                                                                            <i class="far fa-star"></i>
                                                                            Predefinita
                                                                        </span>
                                                                    @endif
                                                                </div>

                                                                <div class="f19-card-brand">
                                                                    @if($brand === 'visa')
                                                                        <img src="{{ asset('img/payment/visa.svg') }}"
                                                                             alt="Visa">
                                                                    @elseif($brand === 'mastercard')
                                                                        <img src="{{ asset('img/payment/mastercard.png') }}"
                                                                             alt="Mastercard">
                                                                    @else
                                                                        <span>{{ strtoupper($brand ?: 'CARD') }}</span>
                                                                    @endif
                                                                </div>
                                                            </div>

                                                            <div class="f19-card-chip">
                                                                <span></span>
                                                            </div>

                                                            <div class="f19-card-number">
                                                                <span>••••</span>
                                                                <span>••••</span>
                                                                <span>••••</span>
                                                                <strong>{{ $card->last4 }}</strong>
                                                            </div>

                                                            <div class="f19-card-bottom">
                                                                <div class="f19-card-holder">
                                                                    <small>TITOLARE</small>
                                                                    <strong>{{ $card->holder_name }}</strong>
                                                                </div>

                                                                <div class="f19-card-expiry">
                                                                    <small>SCADENZA</small>
                                                                    <strong>
                                                                        {{ str_pad($card->exp_month, 2, '0', STR_PAD_LEFT) }}/{{ $card->exp_year }}
                                                                    </strong>
                                                                </div>
                                                            </div>

                                                        </div>

                                                        <div class="f19-card-actions">
                                                            <div class="f19-card-status">
                                                                <i class="far fa-credit-card"></i>
                                                                <div>
                                                                    <strong>
                                                                        {{ $isDefault ? 'Carta predefinita' : 'Carta salvata' }}
                                                                    </strong>
                                                                    <small>
                                                                        {{ $isDefault
                                                                            ? 'Utilizzata come carta preferita'
                                                                            : 'Disponibile per i tuoi acquisti' }}
                                                                    </small>
                                                                </div>
                                                            </div>

                                                            <a href="#"
                                                               class="f19-card-delete delete-card"
                                                               data-id="{{ $card->stripe_payment_method_id }}"
                                                               title="Elimina carta"
                                                               aria-label="Elimina carta"
                                                               data-tooltip="tooltip">
                                                                <i class="far fa-trash-can"></i>
                                                            </a>
                                                        </div>

                                                    </div>

                                                @empty

                                                    <div class="f19-empty-cards">
                                                        <i class="far fa-credit-card"></i>
                                                        <h5>Nessuna carta salvata</h5>
                                                        <p>Aggiungi una carta per i tuoi prossimi acquisti.</p>
                                                    </div>

                                                @endforelse
                                            </div> 
                                            <form method='POST' action='<?php echo $requestUrl ?>'>
                                                <?php foreach ($requestParams as $name => $value) { ?>
                                                    <input type='hidden' name='<?php echo $name; ?>' value='<?php echo htmlentities($value); ?>' />
                                                <?php } ?>                                           
                                                <button type="submit"
                                                        class="f19-add-card addCard">

                                                    <span class="f19-add-card-icon">
                                                        <i class="far fa-plus"></i>
                                                    </span>

                                                    <span class="f19-add-card-text">
                                                        <strong>Aggiungi nuova carta</strong>
                                                        <small>Carta di credito o debito</small>
                                                    </span>

                                                    <i class="far fa-chevron-right f19-add-card-arrow"></i>
                                                </button>
                                            </form>
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

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.querySelectorAll('.delete-card').forEach(btn => {
            btn.addEventListener('click', async () => {

                Swal.fire({
                    title: "Sei sicuro di voler eliminare questo metodo di pagamento?",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Elimina"
                }).then(async (result) => {   // 👈 async qui
                    if (result.isConfirmed) {
                        const id = btn.dataset.id;

                        const res = await fetch(`/payment-method/delete/${id}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            }
                        });

                        const data = await res.json();

                        if (data.success) {
                            location.reload();
                        } else {
                            alert(data.error);
                        }
                    }
                });
            });
        });
    </script>

@endsection