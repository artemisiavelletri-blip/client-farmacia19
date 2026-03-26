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
                                            <h4 class="user-card-title">Modifica Indirizzo di Spedizione</h4>
                                            <div class="user-card-header-right">
                                                <a href="/settings/shipping-address" class="theme-btn"><span class="fas fa-arrow-left"></span>Indirizzo di Spedizione</a>
                                            </div>
                                        </div>
                                        <div class="user-form">
                                            @if ($errors->any())
                                                <div class="alert alert-danger">
                                                    <ul>
                                                        @foreach ($errors->all() as $error)
                                                            <li>{{ $error }}</li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            @endif
                                            <form action="/settings/edit-address/shipping" method="POST">
                                                @csrf
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <div class="form-group">
                                                            <label>Destinatario</label>
                                                            <input type="text" name="recipient_name" class="form-control" placeholder="Destinatario" value="{{$shipping_address->recipient_name}}">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <div class="form-group">
                                                            <label>Indirizzo</label>
                                                            <input type="text" name="address" class="form-control" placeholder="Indirizzo" value="{{Auth::user()->shippingAddresses()->first()->address}}">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label>Note</label>
                                                            <input type="text" name="note" class="form-control" placeholder="Note" value="{{Auth::user()->shippingAddresses()->first()->note}}">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label>CAP</label>
                                                            <input type="text" name="cap" class="form-control" placeholder="CAP" value="{{Auth::user()->shippingAddresses()->first()->cap}}">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label>Città</label>
                                                            <input type="text" id="cityInput2" class="form-control city-autocomplete" placeholder="Scrivi la città..." value="{{Auth::user()->shippingAddressesCity()->name}}">
                                                            <input type="hidden" name="city_id" class="required-private" id="residenceCityId" value="{{Auth::user()->shippingAddresses()->first()->city_id}}">
                                                            <ul class="dropdown-menu"></ul>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label>Telefono</label>
                                                            <input type="text" class="form-control" name="phone" placeholder="Telefono"value="{{Auth::user()->shippingAddresses()->first()->phone}}">
                                                        </div>
                                                    </div>                                                    
                                                </div>
                                                <button type="submit" class="theme-btn"><span class="far fa-save"></span> Salva Indirizzo</button>
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

    <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', () => {

            function setupCityAutocomplete(input, hidden) {
                const dropdown = input.parentElement.querySelector('.dropdown-menu');
                let selected = null;
                let timeout = null;

                input.addEventListener('input', () => {
                    clearTimeout(timeout);
                    const term = input.value.trim();

                    selected = null;
                    hidden.value = '';

                    if(term.length < 2){
                        dropdown.style.display = 'none';
                        return;
                    }

                    timeout = setTimeout(() => {
                        fetch(`/cities/search?q=${encodeURIComponent(term)}`)
                            .then(res => res.json())
                            .then(data => {
                                dropdown.innerHTML = '';
                                if(data.length === 0){
                                    dropdown.style.display = 'none';
                                    return;
                                }

                                data.forEach(city => {
                                    const li = document.createElement('li');
                                    li.textContent = city.name;
                                    li.style.padding = '8px';
                                    li.style.cursor = 'pointer';

                                    li.addEventListener('click', () => {
                                        input.value = city.name;
                                        hidden.value = city.id;
                                        selected = city.name;
                                        dropdown.style.display = 'none';
                                    });

                                    dropdown.appendChild(li);
                                });

                                dropdown.style.display = 'block';
                            });
                    }, 300);
                });

                input.addEventListener('blur', () => {
                    setTimeout(() => {
                        if(!selected){
                            input.value = '';
                            hidden.value = '';
                        }
                        dropdown.style.display = 'none';
                    }, 150);
                });

                // chiudi dropdown cliccando fuori
                document.addEventListener('click', e => {
                    if(!e.target.closest('.form-group')){
                        dropdown.style.display = 'none';
                    }
                });
            }

            // inizializzo tutti gli input con classe city-autocomplete
            document.querySelectorAll('.city-autocomplete').forEach(input => {
                const hidden = input.parentElement.querySelector('input[type="hidden"]');
                setupCityAutocomplete(input, hidden);
            });

        });
    </script>

@endsection