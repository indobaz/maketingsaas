<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @php($title = $title ?? config('app.name', 'Pulsify'))
    @include('layouts.partials/title-meta', ['title' => $title])
    @include('layouts.partials/head-css')
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
