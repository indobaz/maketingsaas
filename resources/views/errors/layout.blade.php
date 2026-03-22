<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Error') — Pulsify</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --color-primary: #5F63F2;
            --color-secondary: #272B41;
            --color-text-muted: #9299B8;
            --color-bg: #F4F5F7;
            --color-danger: #FF4D4F;
            --color-white: #fff;
            --color-border: #E8EAED;
        }

        *, *::before, *::after {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: var(--color-bg);
            color: var(--color-secondary);
            -webkit-font-smoothing: antialiased;
        }

        .error-shell {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 32px 20px 48px;
            position: relative;
            overflow: hidden;
        }

        .error-brand {
            position: absolute;
            top: 24px;
            left: 50%;
            transform: translateX(-50%);
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 0.04em;
            color: var(--color-primary);
            text-transform: uppercase;
        }
    </style>
    @stack('styles')
</head>
<body>
    <div class="error-shell">
        <div class="error-brand">Pulsify</div>
        @yield('content')
    </div>
    @stack('scripts')
</body>
</html>
