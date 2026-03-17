<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Pulsify') }} — Onboarding</title>

    @vite(['resources/scss/icons.scss','resources/scss/app.scss'])
    @vite(['resources/js/config.js'])

    <style>
        :root {
            --color-primary: #5F63F2;
            --color-secondary: #272B41;
            --color-success: #20C997;
            --color-warning: #FA8B0C;
            --color-danger: #FF4D4F;
            --color-bg: #F7F8FA;
            --color-text-muted: #9299B8;
            --font-main: 'Inter', 'Jost', sans-serif;
            --card-radius: 16px;
            --card-border: #EAECF0;
            --card-shadow: 0 1px 3px rgba(0,0,0,0.08);
            --input-border: #D0D5DD;
            --input-radius: 8px;
            --transition: 0.2s ease;
        }

        body {
            font-family: var(--font-main);
            background: var(--color-bg);
            color: #101828;
        }

        .onboarding-shell {
            min-height: 100vh;
        }

        .brand {
            font-weight: 700;
            letter-spacing: -0.5px;
            color: var(--color-primary);
            font-size: 22px;
        }

        .card-soft {
            background: #fff;
            border: 1px solid var(--card-border);
            border-radius: var(--card-radius);
            box-shadow: var(--card-shadow);
        }

        .card-body {
            padding: 40px !important;
        }

        @media (max-width: 575.98px) {
            .card-body {
                padding: 28px !important;
            }
        }

        .step-kicker {
            font-size: 13px;
            font-weight: 500;
            color: var(--color-primary);
            margin-bottom: 10px;
        }

        .form-label {
            font-size: 13px;
            font-weight: 500;
            color: #344054;
            margin-bottom: 6px;
        }

        .form-control,
        .form-select {
            height: 44px;
            border: 1.5px solid var(--input-border);
            border-radius: var(--input-radius);
            padding: 10px 14px;
            font-size: 14px;
            transition: box-shadow var(--transition), border-color var(--transition), background-color var(--transition);
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--color-primary);
            box-shadow: 0 0 0 3px rgba(95,99,242,0.12);
        }

        .btn {
            border-radius: 8px;
            height: 44px;
            font-weight: 500;
            font-size: 14px;
            transition: filter var(--transition), box-shadow var(--transition), transform var(--transition), background-color var(--transition), border-color var(--transition);
        }

        .btn-primary-soft {
            background: var(--color-primary);
            border: none;
            font-weight: 500;
        }

        .btn-primary-soft:hover {
            filter: brightness(1.10);
        }

        .btn-outline-secondary {
            border: 1.5px solid var(--input-border);
            color: #344054;
            background: #fff;
        }

        .btn-outline-secondary:hover {
            background: #F9FAFB;
            border-color: #C7CDD6;
            color: #344054;
        }

        /* Linear-style top progress bar */
        .top-progress {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: #E5E7EB;
            z-index: 50;
        }

        .top-progress > .fill {
            height: 100%;
            width: 0%;
            background: var(--color-primary);
            border-radius: 2px;
            transition: width 0.4s ease;
        }

        .step-indicator {
            display: flex;
            align-items: center;
            justify-content: space-between;
            max-width: 520px;
            margin: 0 auto 24px auto;
            padding: 0 0;
            font-size: 13px;
        }

        .step-indicator .name {
            font-weight: 500;
            color: var(--color-primary);
            font-size: 13px;
        }

        .step-indicator .count {
            font-size: 13px;
            color: #6B7280;
        }
    </style>
</head>
<body>
    @php
        $s = (int) ($step ?? 1);
        $name = (string) ($stepName ?? '');
        $fillWidth = max(0, min(100, (int) round(($s / 3) * 100))).'%';
    @endphp
    <div class="top-progress" aria-hidden="true">
        <div class="fill" style="width: {{ $fillWidth }};"></div>
    </div>

    <main class="onboarding-shell py-4 py-md-5">
        <div class="container px-3">
            <div class="text-center mb-3">
                <div class="brand">Pulsify</div>
            </div>

            <div class="mx-auto" style="max-width: 520px;">
                <div class="step-indicator">
                    <div class="name">{{ $name }}</div>
                    <div class="count">Step {{ $s }} of 3</div>
                </div>

                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif
                @if (session('warning'))
                    <div class="alert alert-warning">{{ session('warning') }}</div>
                @endif

                <div class="card card-soft">
                    <div class="card-body">
                        @yield('content')
                    </div>
                </div>
            </div>
        </div>
    </main>

    @vite(['resources/js/app.js','resources/js/layout.js'])
    @stack('scripts')
</body>
</html>

