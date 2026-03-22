@extends('layouts.auth')

@php
    $maskEmail = function (string $email): string {
        $parts = explode('@', $email, 2);
        if (count($parts) !== 2) return $email;

        [$local, $domain] = $parts;
        $first = substr($local, 0, 1);
        $maskedLocal = $first . str_repeat('*', max(1, strlen($local) - 1));

        return $maskedLocal . '@' . $domain;
    };
@endphp

@section('content')
    @php($title = 'Verify Email')
    <div class="row justify-content-center">
        <div class="col-12 col-sm-10 col-md-8 col-lg-5 col-xl-4">
            <div class="card shadow-sm my-5">
                <div class="card-body p-4 p-lg-5">
                    <div class="text-center mb-4">
                        <div class="auth-wordmark">Pulsify</div>
                        <div class="auth-subtitle">Verify your email</div>
                    </div>

                    @if (session('error'))
                        <div class="alert alert-danger mb-3" role="alert">{{ session('error') }}</div>
                    @endif
                    @if (session('warning'))
                        <div class="alert alert-warning mb-3" role="alert">{{ session('warning') }}</div>
                    @endif
                    @if (session('success'))
                        <div class="alert alert-success mb-3" role="alert">{{ session('success') }}</div>
                    @endif

                    <div class="mb-3 auth-body-muted">
                        We sent a 6-digit code to <span class="fw-semibold auth-card-heading">{{ $maskEmail($email) }}</span>
                    </div>

                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="auth-body-muted" style="font-size: 13px;">
                            Code expires in <span id="otp-timer" class="fw-semibold auth-card-heading">15:00</span>
                        </div>
                    </div>

                    <form method="POST" action="{{ url('/verify-email') }}" novalidate>
                        @csrf

                        <div class="mb-3">
                            <label for="otp" class="form-label">Verification code</label>
                            <input
                                id="otp"
                                name="otp"
                                type="number"
                                inputmode="numeric"
                                pattern="[0-9]*"
                                class="form-control @error('otp') is-invalid @enderror"
                                value="{{ old('otp') }}"
                                placeholder="Enter 6-digit code"
                                required
                            >
                            @error('otp')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            Verify
                        </button>
                    </form>

                    <form method="POST" action="{{ url('/verify-email/resend') }}" class="text-center mt-4">
                        @csrf
                        <button type="submit" class="btn btn-link text-decoration-none p-0">
                            Resend code
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        (function () {
            var totalSeconds = Number(@json(max(1, (int) $otpExpiryMinutes) * 60));
            var el = document.getElementById('otp-timer');

            function pad(n) { return String(n).padStart(2, '0'); }
            function render() {
                var m = Math.floor(totalSeconds / 60);
                var s = totalSeconds % 60;
                if (el) el.textContent = pad(m) + ':' + pad(s);
            }

            render();
            var timer = setInterval(function () {
                totalSeconds = Math.max(0, totalSeconds - 1);
                render();
                if (totalSeconds <= 0) clearInterval(timer);
            }, 1000);
        })();
    </script>
@endsection

