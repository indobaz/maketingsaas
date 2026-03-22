@extends('layouts.auth')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6 col-xl-5">
            <div class="card shadow-sm mt-4">
                <div class="card-body p-4 p-md-5">
                    <div class="text-center mb-4">
                        <div class="auth-wordmark">Pulsify</div>
                        <div class="auth-subtitle">Accept your invitation</div>
                    </div>

                    @if(!empty($error))
                        <div class="alert alert-danger" role="alert">
                            {{ $error }}
                        </div>
                        <div class="text-center">
                            <a href="{{ url('/login') }}" class="text-decoration-none auth-inline-link">
                                Go to login
                            </a>
                        </div>
                    @else
                        <div class="mb-3">
                            <div class="fw-semibold auth-card-heading">You've been invited to join</div>
                            <div class="fw-bold auth-company-highlight" style="font-size: 20px;">
                                {{ $company?->name }}
                            </div>
                        </div>

                        @if (session('error'))
                            <div class="alert alert-danger">{{ session('error') }}</div>
                        @endif

                        <form method="POST" action="{{ url('/invite/accept') }}" novalidate>
                            @csrf
                            <input type="hidden" name="token" value="{{ old('token', $token) }}">

                            <div class="mb-3">
                                <label for="name" class="form-label">Full Name</label>
                                <input
                                    id="name"
                                    name="name"
                                    type="text"
                                    class="form-control @error('name') is-invalid @enderror"
                                    value="{{ old('name') }}"
                                    required
                                >
                                @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input
                                    id="password"
                                    name="password"
                                    type="password"
                                    class="form-control @error('password') is-invalid @enderror"
                                    required
                                >
                                @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label for="password_confirmation" class="form-label">Confirm Password</label>
                                <input
                                    id="password_confirmation"
                                    name="password_confirmation"
                                    type="password"
                                    class="form-control"
                                    required
                                >
                            </div>

                            <button type="submit" class="btn btn-primary w-100"
                                    style="background: #5F63F2; border-color:#5F63F2;">
                                Accept &amp; Join
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

