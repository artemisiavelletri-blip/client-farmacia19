@extends('master')

@section('content')

    <main class="main">

        <!-- login area -->
        <div class="login-area py-90">
            <div class="container">
                <div class="col-md-7 col-lg-5 mx-auto">
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
                        <form action="/reset-password" method="POST">
                            @csrf
                            <input type="hidden" name="token" value="{{ $token }}">
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" class="form-control" placeholder="Email" name="email" value="{{ request()->email }}">
                            </div>
                            <div class="form-group">
                                <label>Password</label>
                                <input type="password" class="form-control" placeholder="Password" name="password">
                            </div>
                            <div class="form-group">
                                <label>Password</label>
                                <input type="password" class="form-control" placeholder="Conferma Password" name="password_confirmation">
                            </div>
                            <div class="d-flex align-items-center">
                                <button type="submit" class="theme-btn"><i class="far fa-sign-in"></i> Reimposta Password</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- login area end -->

    </main>
@endsection