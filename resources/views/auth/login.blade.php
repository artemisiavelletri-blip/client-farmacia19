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

                        @if (session()->has('success'))
                            <div class="alert alert-success">
                                {{ session()->get('success') }}
                            </div>
                        @endif
                        <form action="/login" method="POST">
                            @csrf
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" class="form-control" placeholder="Email" name="email">
                            </div>
                            <div class="form-group">
                                <label>Password</label>
                                <input type="password" class="form-control" placeholder="Password" name="password">
                            </div>
                            <div class="d-flex justify-content-between mb-4 align-right">
                                <a href="/forgot-password" class="forgot-pass">Ho dimenticato la password</a>
                            </div>
                            <div class="d-flex align-items-center">
                                <button type="submit" class="theme-btn"><i class="far fa-sign-in"></i> Accedi</button>
                            </div>
                        </form>
                        <div class="login-footer">
                            <p>Non hai un account? <a href="/register">Registrati.</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- login area end -->

    </main>
@endsection