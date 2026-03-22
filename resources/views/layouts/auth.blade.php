<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @php($title = $title ?? config('app.name', 'Pulsify'))
    @include('layouts.partials/title-meta', ['title' => $title])
    @include('layouts.partials/head-css')
    <style>
        .account-pages .auth-wordmark {
            color: var(--color-primary, #5F63F2);
            font-weight: 700;
            font-size: 22px;
            letter-spacing: 0.2px;
        }
        .account-pages .auth-subtitle {
            color: #6B7280;
            font-size: 14px;
        }
        .account-pages .auth-body-muted {
            color: #6B7280;
            font-size: 14px;
        }
        .account-pages .auth-card-heading {
            color: #272B41;
        }
        .account-pages .card .form-label,
        .account-pages .card .form-check-label {
            color: #6B7280;
        }
        .account-pages .card a.auth-inline-link:not(.btn) {
            color: var(--color-primary, #5F63F2);
        }
        .account-pages .card .btn-link {
            color: var(--color-primary, #5F63F2);
        }
        .account-pages .auth-company-highlight {
            color: var(--color-primary, #5F63F2);
        }
    </style>
</head>

<body class="authentication-bg position-relative">
<div class="account-pages">
    <div class="container">
        @yield('content')
    </div>
</div>

@yield('scripts')
@include('layouts.partials/footer-scripts')
</body>
</html>
