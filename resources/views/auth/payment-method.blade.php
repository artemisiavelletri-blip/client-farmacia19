@extends('master')

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
                                        <div class="user-card-header">
                                            <h4 class="user-card-title">Metodo di Pagamento</h4>
                                            <div class="user-card-header-right">
                                                <a href="/settings/add-payment" class="theme-btn"><span class="far fa-plus-circle"></span>Aggiungi Metodo di Pagamento</a>
                                            </div>
                                        </div>
                                        <div class="table-responsive">
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
                                                                <div class="table-list-img w-25">
                                                                    @if($card->brand == 'visa')
                                                                        <img class="rounded-3" src="{{ asset('/img/payment/visa.svg') }}" alt="">
                                                                    @elseif($card->brand == 'mastercard')
                                                                        <img class="rounded-3" src="{{ asset('/img/payment/mastercard.png') }}" alt="">
                                                                    @else
                                                                        <img class="rounded-3" src="{{ asset('/img/payment/mastercard.png') }}" alt="">
                                                                    @endif
                                                                </div>
                                                            </td>
                                                            <td><span class="table-list-code">{{$card->holder_name}}</span></td>
                                                            <td>***********{{$card->last4}}</td>
                                                            <td>{{$card->exp_month}}/{{$card->exp_year}}</td>
                                                            <td>
                                                                <a href="#" class="btn btn-outline-danger btn-sm rounded-2 delete-card" data-tooltip="tooltip" title="Delete" data-id="{{$card->stripe_payment_method_id}}"><i class="far fa-trash-can"></i></a>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
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