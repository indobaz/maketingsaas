@extends('errors.layout')

@section('title', 'Access denied')

@push('styles')
    <style>
        .err-403-wrap {
            position: relative;
            width: 100%;
            max-width: 480px;
            text-align: center;
        }

        .err-lock {
            font-size: 80px;
            line-height: 1;
            color: var(--color-danger);
            margin: 0 0 20px;
            display: block;
            animation: errLockPulse 2s ease-in-out infinite;
        }

        @keyframes errLockPulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .err-restrict-card {
            max-width: 400px;
            margin: 0 auto 28px;
            background: var(--color-white);
            border-radius: 16px;
            border: 2px solid rgba(255, 77, 79, 0.3);
            box-shadow: 0 4px 24px rgba(255, 77, 79, 0.08);
            overflow: hidden;
            text-align: left;
        }

        .err-restrict-banner {
            background: linear-gradient(90deg, rgba(255, 77, 79, 0.12), rgba(255, 77, 79, 0.06));
            color: #C41D23;
            font-weight: 700;
            font-size: 13px;
            padding: 10px 14px;
            border-bottom: 1px solid rgba(255, 77, 79, 0.2);
        }

        .err-blur-zone {
            position: relative;
            padding: 20px 16px 24px;
            min-height: 120px;
        }

        .err-blur-rows {
            filter: blur(6px);
            opacity: 0.55;
            pointer-events: none;
        }

        .err-blur-line {
            height: 10px;
            border-radius: 4px;
            margin-bottom: 10px;
            background: linear-gradient(90deg, #D8DCE6, #EEF0F4);
        }

        .err-blur-line:nth-child(2) { width: 92%; }
        .err-blur-line:nth-child(3) { width: 78%; }
        .err-blur-line:nth-child(4) { width: 88%; }

        .err-lock-overlay {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px;
        }

        .err-lock-pill {
            background: rgba(255, 255, 255, 0.95);
            border: 1px solid rgba(39, 43, 65, 0.12);
            border-radius: 999px;
            padding: 10px 18px;
            font-size: 13px;
            font-weight: 600;
            color: var(--color-secondary);
            box-shadow: 0 8px 24px rgba(39, 43, 65, 0.12);
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .err-heading {
            font-size: 24px;
            font-weight: 600;
            color: var(--color-secondary);
            margin: 0 0 12px;
            line-height: 1.3;
        }

        .err-sub {
            font-size: 16px;
            color: var(--color-text-muted);
            max-width: 420px;
            margin: 0 auto 16px;
            line-height: 1.55;
        }

        .err-role-badge {
            display: inline-block;
            margin-bottom: 24px;
            padding: 6px 12px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 600;
            text-transform: lowercase;
            background: #EEF0F4;
            color: #64748B;
        }

        .err-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            justify-content: center;
        }

        .btn-err-primary {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 20px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 14px;
            text-decoration: none;
            border: none;
            cursor: pointer;
            background: var(--color-primary);
            color: #fff;
            transition: opacity 0.2s;
        }

        .btn-err-primary:hover {
            opacity: 0.92;
            color: #fff;
        }

        .btn-err-outline {
            display: inline-flex;
            align-items: center;
            padding: 12px 20px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 14px;
            text-decoration: none;
            background: transparent;
            color: var(--color-secondary);
            border: 1px solid #D0D5DD;
        }

        .btn-err-outline:hover {
            background: rgba(39, 43, 65, 0.04);
            color: var(--color-secondary);
        }

        .err-float-sec {
            position: absolute;
            font-size: 22px;
            opacity: 0.32;
            pointer-events: none;
            user-select: none;
        }

        .err-float-sec.s1 { top: 10%; left: 5%; animation: secFloat1 7s ease-in-out infinite; }
        .err-float-sec.s2 { top: 18%; right: 6%; animation: secFloat2 8s ease-in-out infinite; }
        .err-float-sec.s3 { bottom: 30%; left: 3%; animation: secFloat3 9s ease-in-out infinite; }
        .err-float-sec.s4 { bottom: 14%; right: 5%; animation: secFloat4 6s ease-in-out infinite; }

        @keyframes secFloat1 {
            0%, 100% { transform: translate(0, 0); opacity: 0.2; }
            50% { transform: translate(5px, -12px); opacity: 0.45; }
        }
        @keyframes secFloat2 {
            0%, 100% { transform: translate(0, 0); opacity: 0.18; }
            50% { transform: translate(-6px, 10px); opacity: 0.42; }
        }
        @keyframes secFloat3 {
            0%, 100% { transform: translate(0, 0); opacity: 0.22; }
            50% { transform: translate(8px, 6px); opacity: 0.48; }
        }
        @keyframes secFloat4 {
            0%, 100% { transform: translate(0, 0); opacity: 0.2; }
            50% { transform: translate(-5px, -8px); opacity: 0.4; }
        }
    </style>
@endpush

@section('content')
    @php
        $loggedIn = auth()->check();
        $roleLabel = $loggedIn ? (string) (auth()->user()->role ?? 'member') : null;
    @endphp

    <div class="err-403-wrap">
        <span class="err-float-sec s1" aria-hidden="true">🔒</span>
        <span class="err-float-sec s2" aria-hidden="true">🛡️</span>
        <span class="err-float-sec s3" aria-hidden="true">🚫</span>
        <span class="err-float-sec s4" aria-hidden="true">⚠️</span>

        <i class="bi bi-shield-lock-fill err-lock" aria-hidden="true"></i>

        <div class="err-restrict-card">
            <div class="err-restrict-banner">🔒 Private — Access Restricted</div>
            <div class="err-blur-zone">
                <div class="err-blur-rows" aria-hidden="true">
                    <div class="err-blur-line"></div>
                    <div class="err-blur-line"></div>
                    <div class="err-blur-line"></div>
                    <div class="err-blur-line"></div>
                </div>
                <div class="err-lock-overlay">
                    <div class="err-lock-pill">
                        <i class="bi bi-lock-fill text-danger" style="font-size: 14px;"></i>
                        Follow to see content
                    </div>
                </div>
            </div>
        </div>

        <h1 class="err-heading">You're not supposed to be here</h1>
        <p class="err-sub">You don't have permission to view this area. This zone requires a higher clearance level. Contact your workspace admin if you think this is a mistake.</p>

        @if($loggedIn)
            <div class="err-role-badge">Your role: {{ $roleLabel }}</div>
        @else
            <div class="err-role-badge">You are not logged in</div>
        @endif

        <div class="err-actions">
            <button type="button" class="btn-err-primary" onclick="history.back()">← Go Back</button>
            <a href="{{ url('/team') }}" class="btn-err-outline">Contact Admin</a>
        </div>
    </div>
@endsection
