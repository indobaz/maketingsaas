@extends('layouts.auth')

@section('content')
    @php($title = 'Reset Password')
    <div class="row justify-content-center">
        <div class="col-12 col-sm-10 col-md-8 col-lg-5 col-xl-4">
            <div class="card shadow-sm my-5">
                <div class="card-body p-4 p-lg-5">
                    <div class="text-center mb-4">
                        <div class="fw-bold auth-brand text-white" style="font-size: 28px; letter-spacing: .2px;">
                            Pulsify
                        </div>
                        <div class="text-white-50" style="font-size: 14px;">
                            Set new password
                        </div>
                    </div>

                    @if (session('error'))
                        <div class="alert alert-danger mb-3">{{ session('error') }}</div>
                    @endif

                    <form method="POST" action="{{ url('/reset-password') }}" novalidate>
                        @csrf
                        <input type="hidden" name="token" value="{{ old('token', $token) }}">
                        <input type="hidden" name="email" value="{{ old('email', $email) }}">

                        <div class="mb-3">
                            <label for="password" class="form-label text-white">New Password</label>
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
                            <label for="password_confirmation" class="form-label text-white">Confirm Password</label>
                            <input
                                id="password_confirmation"
                                name="password_confirmation"
                                type="password"
                                class="form-control"
                                required
                            >
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            Reset Password
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

