@props([
    'platform',
    'uid',
    'size' => 40,
])
@php
    $s = (int) $size;
@endphp
@switch($platform)
    @case('instagram')
        <svg width="{{ $s }}" height="{{ $s }}" viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg">
            <defs>
                <linearGradient id="{{ $uid }}" x1="0%" y1="100%" x2="100%" y2="0%">
                    <stop offset="0%" stop-color="#f09433"/>
                    <stop offset="25%" stop-color="#e6683c"/>
                    <stop offset="50%" stop-color="#dc2743"/>
                    <stop offset="75%" stop-color="#cc2366"/>
                    <stop offset="100%" stop-color="#bc1888"/>
                </linearGradient>
            </defs>
            <rect width="40" height="40" rx="10" fill="url(#{{ $uid }})"/>
            <rect x="11" y="14" width="18" height="14" rx="3.5" fill="none" stroke="#fff" stroke-width="1.4"/>
            <circle cx="20" cy="21" r="3.8" fill="none" stroke="#fff" stroke-width="1.4"/>
            <circle cx="24.5" cy="16.5" r="1.2" fill="#fff"/>
        </svg>
        @break
    @case('youtube')
        <svg width="{{ $s }}" height="{{ $s }}" viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg">
            <rect width="40" height="40" rx="10" fill="#FF0000"/>
            <polygon points="17,12 17,28 29,20" fill="#fff"/>
        </svg>
        @break
    @case('linkedin')
        <svg width="{{ $s }}" height="{{ $s }}" viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg">
            <rect width="40" height="40" rx="10" fill="#0077B5"/>
            <text x="20" y="25" text-anchor="middle" fill="#fff" font-size="15" font-weight="700" font-family="system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif">in</text>
        </svg>
        @break
    @case('tiktok')
        <svg width="{{ $s }}" height="{{ $s }}" viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg">
            <rect width="40" height="40" rx="10" fill="#010101"/>
            <ellipse cx="16" cy="26" rx="4" ry="3.2" fill="#fff"/>
            <rect x="20.5" y="9" width="3.2" height="17" rx="0.5" fill="#fff"/>
            <path d="M23.7 9.2h2.8v0.2c0 3.2 2.4 5.6 5.6 5.8v3.2c-2.4-0.2-4.5-1.4-5.6-3.4V26h-3V9.2z" fill="#fff"/>
        </svg>
        @break
    @case('facebook')
        <svg width="{{ $s }}" height="{{ $s }}" viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg">
            <rect width="40" height="40" rx="10" fill="#1877F2"/>
            <text x="21" y="27" text-anchor="middle" fill="#fff" font-size="22" font-weight="700" font-family="system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif">f</text>
        </svg>
        @break
    @case('twitter')
        <svg width="{{ $s }}" height="{{ $s }}" viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg">
            <rect width="40" height="40" rx="10" fill="#000000"/>
            <path d="M12 12 L28 28 M28 12 L12 28" stroke="#fff" stroke-width="2.4" stroke-linecap="round"/>
        </svg>
        @break
    @case('pinterest')
        <svg width="{{ $s }}" height="{{ $s }}" viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg">
            <rect width="40" height="40" rx="10" fill="#E60023"/>
            <text x="20" y="26" text-anchor="middle" fill="#fff" font-size="18" font-weight="700" font-family="system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif">P</text>
        </svg>
        @break
    @case('snapchat')
        <svg width="{{ $s }}" height="{{ $s }}" viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg">
            <rect width="40" height="40" rx="10" fill="#FFFC00"/>
            <path fill="#fff" d="M20 10c-3.3 0-5.8 2.4-5.8 5.6v4.2c0 0.9 0.4 1.7 1.1 2.2 0.2 0.15 0.15 0.45-0.05 0.6-0.55 0.35-1.15 0.55-1.75 0.75-0.35 0.12-0.5 0.5-0.35 0.85 0.15 0.4 0.55 0.55 0.95 0.45 0.95-0.25 1.85-0.65 2.65-1.15 0.35-0.2 0.8-0.2 1.15 0 0.85 0.55 1.85 0.95 2.9 1.15 0.4 0.1 0.8-0.05 0.95-0.45 0.15-0.35 0-0.73-0.35-0.85-0.6-0.2-1.2-0.4-1.75-0.75-0.2-0.15-0.25-0.45-0.05-0.6 0.7-0.55 1.1-1.35 1.1-2.2v-4.2C25.8 12.4 23.3 10 20 10z"/>
        </svg>
        @break
    @case('whatsapp')
        <svg width="{{ $s }}" height="{{ $s }}" viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg">
            <rect width="40" height="40" rx="10" fill="#25D366"/>
            <g transform="translate(8,8)" fill="#fff">
                <path d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V21c0 9.39-7.61 17-17 17-9.39 0-17-7.61-17-17 0-1.25.2-2.45.57-3.57.12-.35.03-.75-.24-1.02l-2.2-2.2z"/>
            </g>
        </svg>
        @break
    @case('custom')
    @default
        <svg width="{{ $s }}" height="{{ $s }}" viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg">
            <rect width="40" height="40" rx="10" fill="#6B7280"/>
            <text x="20" y="25" text-anchor="middle" fill="#fff" font-size="19" font-family="system-ui, -apple-system, 'Segoe UI', sans-serif">⚙</text>
        </svg>
@endswitch
