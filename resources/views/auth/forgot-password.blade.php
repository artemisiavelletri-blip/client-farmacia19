@extends('master')

@section('content')


    <main class="main">

        <!-- forgot password -->
        <div class="login-area py-100">
            <div class="container">
                <div class="col-md-5 mx-auto">
                    <div class="login-form">
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
                        <div class="login-header">
                            <img src="{{ asset('img/logo/logo.png') }}" alt="">
                            <p>Recupera Password</p>
                        </div>
                        <form action="/send-reset-email" method="POST">
                            @csrf
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" class="form-control" placeholder="Email" name="email">
                            </div>
                            <div class="d-flex align-items-center">
                                <button type="submit" class="theme-btn"><i class="far fa-key"></i> Conferma</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- forgot password end -->

    </main>

@endsection