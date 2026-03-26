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
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if (session()->has('success'))
                            <div class="alert alert-success">
                                {{ session()->get('success') }}
                            </div>
                        @endif
                        <div class="user-wrapper">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="user-card">
                                        <h4 class="user-card-title">Profilo</h4>
                                        <div class="user-form">
                                            <form action="/settings/edit-user-profile" method="POST">
                                                @csrf
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label>Nome</label>
                                                            <input type="text" class="form-control" name="name" value="{{Auth::user()->name}}"
                                                                placeholder="Nome">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label>Cognome</label>
                                                            <input type="text" class="form-control" name="surname" value="{{Auth::user()->surname}}"
                                                                placeholder="Cognome">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label>Email</label>
                                                            <input type="text" class="form-control"
                                                                value="{{Auth::user()->email}}" name="email" placeholder="Email">
                                                        </div>
                                                    </div>
                                                </div>
                                                <button type="submit" class="theme-btn"><span class="far fa-user"></span> Salva</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-12">
                                    <div class="user-card">
                                        <h4 class="user-card-title">Cambia Password</h4>
                                        <div class="col-lg-12">
                                            <div class="user-form">
                                                <form action="/settings/update-password" method="POST">
                                                    @csrf
                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <div class="form-group">
                                                                <label>Vecchia Password</label>
                                                                <input type="password" name="old_password" class="form-control"
                                                                    placeholder="Vecchia Password">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-12">
                                                            <div class="form-group">
                                                                <label>Nuova Password</label>
                                                                <input type="password" name="new_password" class="form-control"
                                                                    placeholder="Nuova Password">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-12">
                                                            <div class="form-group">
                                                                <label>Ripeti Password</label>
                                                                <input type="password" name="new_password_confirmation" class="form-control"
                                                                    placeholder="Ripeti Password">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <button type="submit" class="theme-btn"><span class="far fa-key"></span> Cambia Password</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-12">
                                    <form id="delete-account-form" action="/delete-account" method="POST">
                                        @csrf
                                        <div class="user-card">
                                            <div class="row user-card-title">
                                                <div class="col-md-6">
                                                    Elimina Account
                                                </div>
                                                <div class="col-md-6 align-right">
                                                    <button id="button-delete-user" type="button" class="theme-btn"><i class="fa fa-trash" aria-hidden="true"></i>  Elimina Account</button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
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
    $('#button-delete-user').on('click',function(){
        Swal.fire({
            title: 'Sei sicuro?',
            text: "Questa azione è irreversibile ed eliminerà definitivamente il tuo account!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sì, elimina account!',
            cancelButtonText: 'Annulla'
        }).then((result) => {
            if (result.isConfirmed) {
                $('#delete-account-form').submit(); // invia il form solo se confermato
            }
        });
    });
    </script>

@endsection