@extends('master')

@section('content')
<main class="main">

    <div class="user-area bg pt-100 pb-80">
        <div class="container">
            <div class="row">
                <div class="col-lg-3">
                    @include('auth.settingsSideBar')
                </div>

                <div class="col-lg-9">
                    <div class="user-wrapper">
                        <div class="user-card">
                            <div class="user-card-header">
                                <h4 class="user-card-title">Aggiungi Metodo di Pagamento</h4>
                                <div class="user-card-header-right">
                                    <a href="/settings/payment-method" class="theme-btn">
                                        <span class="fas fa-arrow-left"></span> Metodi di Pagamento
                                    </a>
                                </div>
                            </div>

                            <div class="user-form">
                                <form id="payment-form">
                                    @csrf

                                    <div class="form-group">
                                        <label>Nome e Cognome</label>
                                        <input type="text" id="card-holder-name" class="form-control" required>
                                    </div>

                                    <div class="form-group">
                                        <label>Numero Carta</label>
                                        <div id="card-number" class="form-control"></div>
                                    </div>

                                    <div class="form-group">
                                        <label>Scadenza</label>
                                        <div id="card-expiry" class="form-control"></div>
                                    </div>

                                    <div class="form-group">
                                        <label>CVC</label>
                                        <div id="card-cvc" class="form-control"></div>
                                    </div>

                                    <div id="card-errors" class="text-danger mt-2"></div>

                                    <button id="card-button" class="theme-btn mt-3">
                                        <span class="far fa-save"></span> Salva Pagamento
                                    </button>
                                </form>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</main>
@endsection

@section('js')
    <script src="https://js.stripe.com/v3/"></script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {

            const stripe = Stripe('{{ config("services.stripe.key") }}');
            const elements = stripe.elements();

            const style = {
                base: {
                    fontSize: '16px',
                    color: '#495057',
                    '::placeholder': { color: '#6c757d' }
                }
            };

            const cardNumber = elements.create('cardNumber', { style });
            const cardExpiry = elements.create('cardExpiry', { style });
            const cardCvc = elements.create('cardCvc', { style });

            cardNumber.mount('#card-number');
            cardExpiry.mount('#card-expiry');
            cardCvc.mount('#card-cvc');

            const form = document.getElementById('payment-form');
            const holderName = document.getElementById('card-holder-name');
            const errors = document.getElementById('card-errors');

            form.addEventListener('submit', async (e) => {
                e.preventDefault();

                const { paymentMethod, error } = await stripe.createPaymentMethod({
                    type: 'card',
                    card: cardNumber,
                    billing_details: { name: holderName.value }
                });

                if (error) {
                    errors.textContent = error.message;
                    return;
                }

                const response = await fetch('{{ route("payment-method.store") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('input[name=_token]').value
                    },
                    body: JSON.stringify({
                        payment_method_id: paymentMethod.id,
                        holderName: $('#card-holder-name').val()
                    })
                });

                const data = await response.json();

                if (data.success) {
                    window.location.href = '/settings/payment-method';
                } else {
                    errors.textContent = data.error;
                }
            });
        });
    </script>

@endsection