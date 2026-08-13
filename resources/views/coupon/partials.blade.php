<style type="text/css">
    #copyCoupon {
        border: 0;
        border-radius: 5px;
        padding: 8px 15px;
        background: #198754;
        color: #fff;
        cursor: pointer;
    }

    #copyCoupon:hover {
        opacity: 0.9;
    }
</style>

<div class="row g-4">
    @forelse($promotion as $coupon)
        <div class="col-md-6 col-lg-4">
            <div class="product-item box-cliccabile" id="openModal" style="cursor: pointer;" data-id="{{$coupon->token}}">
                <div class="text-center">
                    <img src="{{ asset('/img/logo/logo.png') }}" style="width: 50%;">
                </div>

                <div class="product-content text-center">
                    <h3 class="product-title">
                        Sconto 
                        @if($coupon->percentage)
                            {{round($coupon->percentage)}}%
                        @else
                            {{round($coupon->fixDiscount)}}€
                        @endif
                    </h3>

                    <div class="product-bottom mt-10 text-center">
                        {{$coupon->description}}
                    </div>
                    <div class="shop-single-action">
                        <div class="row align-items-center" style="text-align: right;">
                            <div class="col-md-12">
                                <div class="shop-single-btn">
                                    @if($coupon->end_date)
                                        <small><em>Scadenza {{\Carbon\Carbon::parse($coupon->end_date)->format('d-m-Y')}}</em></small>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <p>Nessun coupon trovato</p>
        </div>
    @endforelse
</div>

<div class="mt-30">
    {{ $promotion->links() }}
</div>

<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">

            <div class="modal-header" style="position: relative;">
                <h5 class="modal-title">Sconto 20€</h5>

                <button type="button"
                        id="closeScontoModal" 
                        class="close"
                        data-dismiss="modal"
                        style="position: absolute;right: 15px;top: 50%;transform: translateY(-50%);border: 0;background: transparent;font-size: 28px;line-height: 1;color: #666;cursor: pointer;padding: 5px 10px;">
                    <span>&times;</span>
                </button>
            </div>

            <div class="modal-body text-center">
                <div class="mt-30">
                    <h6 id="description"></h6>
                </div>
                <div class="mt-30 text-center coupon-code" style="border: 1px dashed;padding: 20px;">
                    <h6 id="couponCode">BENVENUTO20</h6>
                </div>
                <button class="mt-30 mb-10" type="button" id="copyCoupon" style="width: 100%">
                    Copia codice
                </button>
                <span class="mt-30">Utilizza il codice al checkout!</span>
            </div>

        </div>
    </div>
</div>

@section('js')
    
    <script type="text/javascript">
            
        $('.box-cliccabile').on('click', function () {
            var token = $(this).data('id');
            $.ajax({
                url: "{{ route('coupon.get') }}",
                type: "POST",
                data: {
                    token: token,
                    _token: "{{ csrf_token() }}"
                },
                success: function(response) {

                    if (response.success) {

                        $('#couponCode').text(response.coupon.name);

                        if(response.coupon.fixDiscount) {

                            $('.modal-title').text('Sconto ' + parseFloat(response.coupon.fixDiscount) + '€');

                        } else {

                            $('.modal-title').text('Sconto ' + parseFloat(response.coupon.percentage) + '%');

                        }

                        $('#description').text(response.coupon.description);

                        $('#exampleModal').modal('show');

                    } else {

                        alert(response.message);

                    }
                },
                error: function(xhr) {

                    alert(
                        xhr.responseJSON?.message ?? 'Errore durante il recupero del coupon.'
                    );

                }
            });            
        });

        $('#closeScontoModal').on('click', function () {
            $('#exampleModal').modal('hide');
        });

        $('#copyCoupon').on('click', function () {

            const code = $('#couponCode').text().trim();

            navigator.clipboard.writeText(code).then(function () {

                $('#copyCoupon').text('Codice copiato');

                setTimeout(function () {
                    $('#copyCoupon').text('Copia codice');
                }, 2000);

            });

        });

    </script>

@endsection