@extends('layouts.dashboard')

@php
    $pageTitle = 'Platform Email Settings';
@endphp

@section('content')
    <p class="text-muted small mb-3">Manage global SMTP used by all companies without SMTP.</p>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="alert alert-warning">
        These are Pulsify platform-level settings. Changes affect ALL companies that haven't configured their own SMTP.
    </div>

    <div class="card border-0 shadow-sm" style="border-radius: 12px; border: 1px solid #F0F2F5 !important;">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                <h5 class="fw-semibold mb-0">Platform SMTP</h5>
                @if($smtpConfigured)
                    <span class="badge bg-success">Configured</span>
                @else
                    <span class="badge bg-warning text-dark">Not configured</span>
                @endif
            </div>

            <form method="POST" action="{{ route('super-admin.smtp.update') }}" id="platformSmtpForm">
                @csrf
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label">SMTP Host</label>
                        <input type="text" name="smtp_host" class="form-control @error('smtp_host') is-invalid @enderror"
                               value="{{ old('smtp_host', $smtpForm['host'] ?? '') }}" placeholder="smtp.gmail.com" required>
                        @error('smtp_host')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">SMTP Port</label>
                        <input type="number" name="smtp_port" class="form-control @error('smtp_port') is-invalid @enderror"
                               value="{{ old('smtp_port', $smtpForm['port'] ?? 587) }}" required>
                        @error('smtp_port')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">SMTP Username</label>
                        <input type="text" name="smtp_username" class="form-control @error('smtp_username') is-invalid @enderror"
                               value="{{ old('smtp_username', $smtpForm['username'] ?? '') }}" required>
                        @error('smtp_username')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">SMTP Password</label>
                        <input type="password" name="smtp_password" class="form-control @error('smtp_password') is-invalid @enderror"
                               placeholder="{{ $smtpConfigured ? 'Enter password to update' : '' }}" required autocomplete="new-password">
                        @error('smtp_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">From Email</label>
                        <input type="email" name="smtp_from_email" class="form-control @error('smtp_from_email') is-invalid @enderror"
                               value="{{ old('smtp_from_email', $smtpForm['from_email'] ?? '') }}" required>
                        @error('smtp_from_email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">From Name</label>
                        <input type="text" name="smtp_from_name" class="form-control @error('smtp_from_name') is-invalid @enderror"
                               value="{{ old('smtp_from_name', $smtpForm['from_name'] ?? '') }}" required>
                        @error('smtp_from_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="d-flex gap-2 flex-wrap mt-4">
                    <button type="submit" class="btn btn-primary">Save Platform SMTP</button>
                    <button type="button" class="btn btn-outline-secondary" id="platformSmtpTestBtn">Send Test Email</button>
                </div>
            </form>

            <div class="small mt-3 d-none" id="platformSmtpTestResult"></div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        (function () {
            var testBtn = document.getElementById('platformSmtpTestBtn');
            var resultEl = document.getElementById('platformSmtpTestResult');
            if (!testBtn || !resultEl) return;

            testBtn.addEventListener('click', function () {
                testBtn.disabled = true;
                resultEl.classList.remove('d-none', 'text-success', 'text-danger', 'text-muted');
                resultEl.classList.add('text-muted');
                resultEl.textContent = 'Sending test email...';

                fetch('{{ route('super-admin.smtp.test') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({})
                }).then(function (response) {
                    return response.json();
                }).then(function (data) {
                    resultEl.classList.remove('text-muted');
                    if (data.success) {
                        resultEl.classList.add('text-success');
                    } else {
                        resultEl.classList.add('text-danger');
                    }
                    resultEl.textContent = data.message || 'No response';
                }).catch(function () {
                    resultEl.classList.remove('text-muted');
                    resultEl.classList.add('text-danger');
                    resultEl.textContent = 'Network error while sending test email.';
                }).finally(function () {
                    testBtn.disabled = false;
                });
            });
        })();
    </script>
@endsection
