<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Pulsify') }}</title>

    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 64 64'%3E%3Crect width='64' height='64' rx='14' fill='%235F63F2'/%3E%3Ctext x='50%25' y='50%25' text-anchor='middle' dominant-baseline='central' font-family='Arial, sans-serif' font-size='34' font-weight='800' fill='white'%3EP%3C/text%3E%3C/svg%3E">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Jost:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root {
            --color-primary: #5F63F2;
            --color-secondary: #272B41;
            --color-success: #20C997;
            --color-warning: #FA8B0C;
            --color-danger: #FF4D4F;
            --color-bg: #F4F5F7;
            --color-text-muted: #9299B8;
            --font-main: 'Jost', sans-serif;
            --sidebar-width: 260px;
            --border-radius: 10px;
            --card-shadow: 0 5px 20px rgba(146,153,184,0.13);
        }

        body {
            font-family: var(--font-main);
            background: var(--color-bg);
        }
    </style>
</head>
<body>
    <main>
        @yield('content')
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.49.1/dist/apexcharts.min.js"></script>

    @stack('scripts')
</body>
</html>

