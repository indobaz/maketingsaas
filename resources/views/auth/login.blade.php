@extends('layouts.auth')

@section('content')
    @php($title = 'Login')
    <div class="row justify-content-center">
        <div class="col-12 col-sm-10 col-md-8 col-lg-5 col-xl-4">
            <div class="card shadow-sm my-5">
                <div class="card-body p-4 p-lg-5">
                    <div class="text-center mb-4">
                        <div class="fw-bold auth-brand text-white" style="font-size: 28px; letter-spacing: .2px;">
                            Pulsify
                        </div>
                        <div class="text-white-50" style="font-size: 14px;">
                            Sign in to your account
                        </div>
                    </div>

                    @if (session('success'))
                        <div class="alert alert-success mb-3" role="alert">{{ session('success') }}</div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger mb-3" role="alert">{{ session('error') }}</div>
                    @endif
                    @if (session('error_html'))
                        <div class="alert alert-danger mb-3" role="alert">{!! session('error_html') !!}</div>
                    @endif

                    <form method="POST" action="{{ url('/login') }}" novalidate>
                        @csrf

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input
                                id="email"
                                name="email"
                                type="email"
                                class="form-control @error('email') is-invalid @enderror"
                                value="{{ old('email') }}"
                                autocomplete="email"
                                required
                            >
                            @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-2">
                            <label for="password" class="form-label">Password</label>
                            <input
                                id="password"
                                name="password"
                                type="password"
                                class="form-control @error('password') is-invalid @enderror"
                                autocomplete="current-password"
                                required
                            >
                            @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex align-items-center justify-content-between mb-4">
                            <div class="form-check">
                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    id="remember"
                                    name="remember"
                                    value="1"
                                    {{ old('remember') ? 'checked' : '' }}
                                >
                                <label class="form-check-label" for="remember">
                                    Remember me
                                </label>
                            </div>
                            <a href="{{ url('/forgot-password') }}" class="text-decoration-none">
                                Forgot password?
                            </a>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            Login
                        </button>
                    </form>

                    <div class="text-center mt-4">
                        <span class="text-muted">Don't have an account?</span>
                        <a href="{{ url('/register') }}" class="text-decoration-none">
                            Sign up
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

