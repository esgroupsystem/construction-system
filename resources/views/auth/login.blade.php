@extends('layouts.auth')

@section('title', 'Login')

@section('content')
    <div class="row flex-center min-vh-100 py-6">
        <div class="col-sm-10 col-md-8 col-lg-6 col-xl-5 col-xxl-4">
            <a class="d-flex flex-center mb-4" href="{{ url('/') }}">
                <img class="me-2" src="{{ asset('assets/img/icons/spot-illustrations/falcon.png') }}" alt=""
                    width="58" />
                <span class="font-sans-serif fw-bolder fs-5 d-inline-block">Construction System</span>
            </a>

            <div class="card">
                <div class="card-body p-4 p-sm-5">
                    <div class="row flex-between-center mb-2">
                        <div class="col-auto">
                            <h5>Log in</h5>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <div class="mb-3">
                            <input class="form-control @error('email') is-invalid @enderror" type="email" name="email"
                                value="{{ old('email') }}" placeholder="Email address" required autofocus>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <input class="form-control @error('password') is-invalid @enderror" type="password"
                                name="password" placeholder="Password" required>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row flex-between-center">
                            <div class="col-auto">
                                <div class="form-check mb-0">
                                    <input class="form-check-input" type="checkbox" name="remember" id="remember">
                                    <label class="form-check-label mb-0" for="remember">Remember me</label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <button class="btn btn-primary d-block w-100 mt-3" type="submit">
                                Log in
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
