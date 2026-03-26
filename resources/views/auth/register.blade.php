<@extends('master')

@section('content')

    <main class="main">


        <!-- register area -->
        <div class="login-area py-100">
            <div class="container">
                <div class="col-md-5 mx-auto">
                    <div class="login-form">
                        <div class="login-header">
                            <img src="{{ asset('/img/logo/logo.png') }}" alt="">
                        </div>
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <form id="registerForm" action="{{ asset('/register') }}" method="POST">
                            @csrf
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="user_type" id="privateUser" value="0" {{ old('user_type', '0') == '0' ? 'checked' : '' }}>
                                <label class="form-check-label" for="privateUser">
                                    Registrati come privato
                                </label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="user_type" value="1" id="companyUser" {{ old('user_type') == '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="companyUser">
                                    Registrati come azienda
                                </label>
                            </div>
                            <div class="login-footer">                         
                            </div>
                            <p>Dati account</p>
                            <div class="form-group">
                                <input type="email" class="form-control" placeholder="Email" name="email" value="{{ old('email') }}">
                            </div>
                            <div class="form-group">
                                <input type="password" class="form-control" placeholder="Password" name="password" value="{{ old('password') }}">
                            </div>
                            <div class="privateUser">                                
                                <p>Indirizzo di fatturazione</p>                                
                                <div class="form-group">
                                    <input type="text" class="form-control required-private" placeholder="Nome" name="private_name" value="{{ old('private_name') }}">
                                </div>
                                <div class="form-group">
                                    <input type="text" class="form-control required-private" placeholder="Cognome" name="private_surname" value="{{ old('private_surname') }}">
                                </div>
                                <div class="form-group">
                                    <input type="text" class="form-control required-private" placeholder="Codice Fiscale" name="private_cf" value="{{ old('private_cf') }}">
                                </div>
                                <div class="form-group">
                                    <input type="text" class="form-control required-private" placeholder="Indirizzo e Numero Civico" name="private_address" value="{{ old('private_address') }}">
                                </div>
                                <div class="form-group">
                                    <input type="text" class="form-control" placeholder="Note Indirizzo" name="private_note" value="{{ old('private_note') }}">
                                </div>
                                <div class="form-group row">
                                    <div class="col-8">
                                        <div class="form-group position-relative width-100">
                                            <input type="text" id="cityInput1" class="form-control city-autocomplete" placeholder="Scrivi la città...">
                                            <input type="hidden" name="private_city_id" class="required-private" id="birthCityId">
                                            <ul class="dropdown-menu"></ul>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <input type="text" class="form-control required-private" placeholder="CAP" name="private_cap" value="{{ old('private_cap') }}">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <input type="text" class="form-control required-private" placeholder="Telefono" name="private_phone" value="{{ old('private_phone') }}">
                                </div>
                                <p>Indirizzo di spedizione</p>
                                <div class="form-check">
                                    <input type="hidden" name="private_check_second_address" value="0">
                                    <input class="form-check-input" type="checkbox" value="1" id="addressChek" name="private_check_second_address" {{ old('private_check_second_address', '1') == '1' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="addressChek">
                                        Uguale a indirizzo di fatturazione
                                    </label>
                                </div>
                                <div class="notSame hidden">
                                    <div class="form-group">
                                        <input type="text" class="form-control required-private" placeholder="Destinatario" name="private_delivery" value="{{ old('private_delivery') }}">
                                    </div>
                                    <div class="form-group">
                                        <input type="text" class="form-control required-private" placeholder="Indirizzo e Numero Civico" name="private_second_address" value="{{ old('private_second_address') }}">
                                    </div>
                                    <div class="form-group">
                                        <input type="text" class="form-control" placeholder="Note Indirizzo" name="private_second_note" value="{{ old('private_second_note') }}">
                                    </div>
                                    <div class="form-group row">
                                        <div class="col-8">
                                            <div class="form-group position-relative width-100">
                                                <input type="text" id="cityInput2" class="form-control city-autocomplete" placeholder="Scrivi la città...">
                                                <input type="hidden" name="private_second_city_id" class="required-private" id="residenceCityId">
                                                <ul class="dropdown-menu"></ul>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <input type="text" class="form-control required-private" placeholder="CAP" name="private_second_cap" value="{{ old('private_second_cap') }}">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <input type="text" class="form-control required-private" placeholder="Telefono" name="private_second_phone" value="{{ old('private_second_phone') }}">
                                    </div>
                                </div>                             
                            </div>
                            <div class="companyUser hidden">                                
                                <p>Indirizzo di fatturazione</p>
                                <div class="form-group">
                                    <input type="text" class="form-control required-company" placeholder="Ragione Sociale" name="company_society" value="{{ old('company_society') }}">
                                </div>                                
                                <div class="form-group">
                                    <input type="text" class="form-control required-company" placeholder="Nome" name="company_name" value="{{ old('company_name') }}">
                                </div>
                                <div class="form-group">
                                    <input type="text" class="form-control required-company" placeholder="Cognome" name="company_surname" value="{{ old('company_surname') }}">
                                </div>
                                <div class="form-group">
                                    <input type="text" class="form-control required-company" placeholder="Codice Fiscale" name="company_cf" value="{{ old('company_cf') }}">
                                </div>
                                <div class="form-group">
                                    <input type="text" class="form-control required-company" placeholder="Patita Iva" name="company_pi" value="{{ old('company_pi') }}">
                                </div>
                                <div class="form-group">
                                    <input type="text" class="form-control company-sdi" placeholder="Codice Univoco SDI" name="company_sdi" value="{{ old('company_sdi') }}">
                                </div>
                                <div class="form-group">
                                    <input type="text" class="form-control company-pec" placeholder="Indirizzo PEC" name="company_pec" value="{{ old('company_pec') }}">
                                </div>
                                <div class="form-group">
                                    <input type="text" class="form-control required-company" placeholder="Indirizzo e Numero Civico" name="company_address" value="{{ old('company_address') }}">
                                </div>
                                <div class="form-group">
                                    <input type="text" class="form-control" placeholder="Note Indirizzo" name="company_note" value="{{ old('company_note') }}">
                                </div>
                                <div class="form-group row">
                                    <div class="col-8">
                                        <div class="form-group position-relative width-100">
                                            <input type="text" id="cityInput1" class="form-control city-autocomplete required-company" placeholder="Scrivi la città...">
                                            <input type="hidden" name="company_city_id" id="birthCityId">
                                            <ul class="dropdown-menu"></ul>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <input type="text" class="form-control required-company" placeholder="CAP" name="company_cap" value="{{ old('company_cap') }}">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <input type="text" class="form-control" placeholder="Telefono" name="company_phone" value="{{ old('company_phone') }}">
                                </div>
                                <p>Indirizzo di spedizione</p>
                                <div class="form-check">
                                    <input type="hidden" name="company_check_second_address" value="0">
                                    <input class="form-check-input" type="checkbox" value="1" id="addressCheckCompany" name="company_check_second_address" {{ old('company_check_second_address', '1') == '1' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="addressChek">
                                        Uguale a indirizzo di fatturazione
                                    </label>
                                </div>
                                <div class="notSameCompany hidden">
                                    <div class="form-group">
                                        <input type="text" class="form-control required-company" placeholder="Ragione Sociale" name="company_second_society" value="{{ old('company_second_society') }}">
                                    </div>
                                    <div class="form-group">
                                        <input type="text" class="form-control required-company" placeholder="Indirizzo e Numero Civico" name="company_second_address" value="{{ old('company_second_address') }}">
                                    </div>
                                    <div class="form-group">
                                        <input type="text" class="form-control" placeholder="Note Indirizzo" name="company_second_note" value="{{ old('company_second_note') }}">
                                    </div>
                                    <div class="form-group row">
                                        <div class="col-8">
                                            <div class="form-group position-relative width-100">
                                                <input type="text" id="cityInput2" class="form-control city-autocomplete required-company" placeholder="Scrivi la città...">
                                                <input type="hidden" name="company_second_city_id" id="residenceCityId">
                                                <ul class="dropdown-menu"></ul>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <input type="text" class="form-control required-company" placeholder="CAP" name="company_second_cap" value="{{ old('company_second_cap') }}">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <input type="text" class="form-control required-company" placeholder="Telefono" name="company_second_phone" value="{{ old('company_second_phone') }}">
                                    </div>
                                </div>
                            </div>
                            <div class="form-check form-group">
                                <input class="form-check-input required-company" type="checkbox" value="1" id="agree" name="terms_service">
                                <label class="form-check-label" for="agree">
                                   Accetto i <a href="#">Terms Of Service.</a>
                                </label>
                            </div>
                            <div class="d-flex align-items-center">
                                <button type="submit" class="theme-btn"><i class="far fa-paper-plane"></i> Registrati</button>
                            </div>
                        </form>
                        <div class="login-footer">
                            <p>Hai già un account? <a href="/login">Accedi.</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- register area end -->
        
    </main>
@endsection
@section('js')
    <script>
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

        $( document ).ready(function() {
            if(!$('#addressChek').is(':checked')) {
                $('.notSame').removeClass('hidden'); // mostra div se checkbox disabilitata
            } else {
                $('.notSame').addClass('hidden'); // nasconde div se checkbox abilitata
            }

            if(!$('#addressCheckCompany').is(':checked')) {
                $('.notSameCompany').removeClass('hidden'); // mostra div se checkbox disabilitata
            } else {
                $('.notSameCompany').addClass('hidden'); // nasconde div se checkbox abilitata
            }

            if(!$('#privateUser').is(':checked')) {
                $('.companyUser').removeClass('hidden'); 
            }
        });


        $('#addressChek').on('change',function(){
            if(!$('#addressChek').is(':checked')) {
                $('.notSame').removeClass('hidden'); // mostra div se checkbox disabilitata
            } else {
                $('.notSame').addClass('hidden'); // nasconde div se checkbox abilitata
            }
        });

        $('#addressCheckCompany').on('change',function(){
            if(!$('#addressCheckCompany').is(':checked')) {
                $('.notSameCompany').removeClass('hidden'); // mostra div se checkbox disabilitata
            } else {
                $('.notSameCompany').addClass('hidden'); // nasconde div se checkbox abilitata
            }
        });

        $('#privateUser').on('change',function(){
            if($(this).prop('checked')){
                $('.privateUser').removeClass('hidden');
                $('.companyUser').addClass('hidden');
            } else {
                $('.privateUser').addClass('hidden');
                $('.companyUser').removeClass('hidden');
            }
        });

        $('#companyUser').on('change',function(){
            if($(this).prop('checked')){
                $('.privateUser').addClass('hidden');
                $('.companyUser').removeClass('hidden');               
            } else {
                $('.privateUser').removeClass('hidden');
                $('.companyUser').addClass('hidden');
            }
        })

        document.addEventListener('DOMContentLoaded', () => {

            const form = document.getElementById('registerForm');
            if (!form) return;

            const privateRadio = document.getElementById('privateUser');
            const companyRadio = document.getElementById('companyUser');

            const privateBox = document.querySelector('.privateUser');
            const companyBox = document.querySelector('.companyUser');

            // Checkbox per indirizzo di spedizione
            const privateCheck = document.getElementById('addressChek');
            const companyCheck = document.getElementById('addressCheckCompany');

            const privateShipping = document.querySelector('.notSame');
            const companyShipping = document.querySelector('.notSameCompany');

            /* ---------- TOGGLE SEZIONI ---------- */
            function toggleUserType() {
                if (privateRadio.checked) {
                    privateBox.style.display = 'block';
                    companyBox.style.display = 'none';
                } else if (companyRadio.checked) {
                    privateBox.style.display = 'none';
                    companyBox.style.display = 'block';
                }
            }

            privateRadio.addEventListener('change', toggleUserType);
            companyRadio.addEventListener('change', toggleUserType);
            toggleUserType(); // inizializza la vista

            // Toggle indirizzo spedizione
            function toggleShipping(checkbox, container) {
                if (!checkbox || !container) return;
                container.style.display = checkbox.checked ? 'none' : 'block';
            }

            privateCheck?.addEventListener('change', () => toggleShipping(privateCheck, privateShipping));
            companyCheck?.addEventListener('change', () => toggleShipping(companyCheck, companyShipping));

            // inizializza shipping
            toggleShipping(privateCheck, privateShipping);
            toggleShipping(companyCheck, companyShipping);

            /* ---------- ERRORI ---------- */
            function showError(input, message = 'Campo richiesto') {
                input.classList.add('is-invalid');

                if (input.nextElementSibling?.classList.contains('invalid-feedback')) return;

                const error = document.createElement('div');
                error.className = 'invalid-feedback';
                error.textContent = message;
                input.parentNode.appendChild(error);
            }

            function clearError(input) {
                input.classList.remove('is-invalid');
                if (input.nextElementSibling?.classList.contains('invalid-feedback')) {
                    input.nextElementSibling.remove();
                }
            }

            /* ---------- SUBMIT ---------- */
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                let valid = true;

                form.querySelectorAll('.is-invalid').forEach(clearError);

                // Campi sempre obbligatori
                form.querySelectorAll('.required-always').forEach(input => {
                    if (!input.value.trim()) {
                        showError(input);
                        valid = false;
                    }
                });

                // PRIVATO
                if (privateRadio.checked) {
                    form.querySelectorAll('.required-private').forEach(input => {
                        if (!input.offsetParent) return; // ignora campi nascosti
                        if (input.type === 'hidden') return;
                        if (!input.value.trim()) {
                            showError(input);
                            valid = false;
                        }
                    });
                }

                // AZIENDA
                if (companyRadio.checked) {
                    form.querySelectorAll('.required-company').forEach(input => {
                        if (!input.offsetParent) return; // ignora campi nascosti
                        if (!input.value.trim()) {
                            showError(input);
                            valid = false;
                        }
                    });

                    // PEC / SDI → almeno uno obbligatorio
                    const pec = form.querySelector('.company-pec');
                    const sdi = form.querySelector('.company-sdi');

                    if ((!pec?.value.trim()) && (!sdi?.value.trim())) {
                        if (pec) showError(pec, 'Inserisci PEC o SDI');
                        if (sdi) showError(sdi, 'Inserisci PEC o SDI');
                        valid = false;
                    }
                }

                if (!valid) {
                    form.querySelector('.is-invalid')?.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                    return;
                }

                form.submit();
            });

            /* ---------- RIMUOVE ERRORI AL DIGITARE ---------- */
            form.querySelectorAll('input').forEach(input => {
                input.addEventListener('input', () => clearError(input));
            });
        });

    </script>
@endsection