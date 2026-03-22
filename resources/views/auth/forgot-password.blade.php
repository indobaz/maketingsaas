@extends('layouts.auth')

@section('content')
    @php($title = 'Forgot Password')
    <div class="row justify-content-center">
        <div class="col-12 col-sm-10 col-md-8 col-lg-5 col-xl-4">
            <div class="card shadow-sm my-5">
                <div class="card-body p-4 p-lg-5">
                    <div class="text-center mb-4">
                        <div class="auth-wordmark">Pulsify</div>
                        <div class="auth-subtitle">Forgot your password?</div>
                    </div>

                    <div class="auth-body-muted mb-3">
                        Enter your email and we'll send you a reset link
                    </div>

                    @if (session('success'))
                        <div class="alert alert-success mb-3">{{ session('success') }}</div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger mb-3">{{ session('error') }}</div>
                    @endif

                    <form method="POST" action="{{ url('/forgot-password') }}" novalidate>
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

                        <button type="submit" class="btn btn-primary w-100">
                            Send Reset Link
                        </button>
                    </form>

                    <div class="text-center mt-4">
                        <a href="{{ url('/login') }}" class="text-decoration-none auth-inline-link">
                            &larr; Back to sign in
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

