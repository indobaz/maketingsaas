@extends('errors.layout')

@section('title', 'Page not found')

@push('styles')
    <style>
        .err-404-wrap {
            position: relative;
            width: 100%;
            max-width: 520px;
            text-align: center;
        }

        .err-404-num {
            font-size: clamp(100px, 22vw, 160px);
            font-weight: 800;
            letter-spacing: -8px;
            line-height: 0.9;
            color: var(--color-primary);
            margin: 0 0 8px;
            animation: err404Float 3s ease-in-out infinite;
        }

        @keyframes err404Float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        .err-post-card {
            max-width: 400px;
            margin: 0 auto 28px;
            background: var(--color-white);
            border-radius: 16px;
            box-shadow: 0 4px 24px rgba(39, 43, 65, 0.08);
            border: 1px solid var(--color-border);
            overflow: hidden;
            text-align: left;
        }

        .err-post-head {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 14px 16px;
            border-bottom: 1px solid var(--color-border);
        }

        .err-post-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--color-primary);
            color: #fff;
            display: grid;
            place-items: center;
            font-weight: 700;
            font-size: 16px;
        }

        .err-post-meta {
            flex: 1;
            min-width: 0;
        }

        .err-post-name {
            font-weight: 600;
            font-size: 14px;
            color: var(--color-secondary);
        }

        .err-post-time {
            font-size: 12px;
            color: var(--color-text-muted);
        }

        .err-broken-media {
            margin: 16px;
            border-radius: 12px;
            background: #EEF0F4;
            min-height: 140px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 8px;
            color: var(--color-text-muted);
            font-size: 13px;
            text-align: center;
            padding: 16px;
        }

        .err-broken-media .warn {
            font-size: 28px;
            line-height: 1;
        }

        .err-caption {
            padding: 0 16px 12px;
            min-height: 28px;
            font-size: 14px;
            color: var(--color-secondary);
        }

        .err-cursor {
            display: inline-block;
            width: 2px;
            height: 16px;
            background: var(--color-primary);
            margin-left: 2px;
            vertical-align: middle;
            animation: errBlink 1s step-end infinite;
        }

        @keyframes errBlink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0; }
        }

        .err-engage {
            display: flex;
            gap: 20px;
            padding: 12px 16px 16px;
            font-size: 13px;
            color: var(--color-text-muted);
            border-top: 1px solid var(--color-border);
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
            max-width: 400px;
            margin: 0 auto 28px;
            line-height: 1.5;
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
            transition: opacity 0.2s, transform 0.15s;
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
            transition: background 0.2s, border-color 0.2s;
        }

        .btn-err-outline:hover {
            background: rgba(39, 43, 65, 0.04);
            color: var(--color-secondary);
        }

        .err-float {
            position: absolute;
            font-size: 22px;
            opacity: 0.35;
            pointer-events: none;
            user-select: none;
        }

        .err-float-1 { top: 12%; left: 6%; animation: errFloatA 7s ease-in-out infinite; }
        .err-float-2 { top: 22%; right: 8%; animation: errFloatB 8s ease-in-out infinite; }
        .err-float-3 { bottom: 28%; left: 4%; animation: errFloatC 9s ease-in-out infinite; }
        .err-float-4 { bottom: 18%; right: 6%; animation: errFloatD 6.5s ease-in-out infinite; }
        .err-float-5 { top: 45%; right: 4%; animation: errFloatA 10s ease-in-out infinite reverse; }

        @keyframes errFloatA {
            0%, 100% { transform: translate(0, 0); opacity: 0.25; }
            50% { transform: translate(6px, -14px); opacity: 0.5; }
        }
        @keyframes errFloatB {
            0%, 100% { transform: translate(0, 0); opacity: 0.2; }
            50% { transform: translate(-8px, 12px); opacity: 0.45; }
        }
        @keyframes errFloatC {
            0%, 100% { transform: translate(0, 0); opacity: 0.3; }
            50% { transform: translate(10px, 8px); opacity: 0.55; }
        }
        @keyframes errFloatD {
            0%, 100% { transform: translate(0, 0); opacity: 0.22; }
            50% { transform: translate(-6px, -10px); opacity: 0.48; }
        }
    </style>
@endpush

@section('content')
    <div class="err-404-wrap">
        <span class="err-float err-float-1" aria-hidden="true">📸</span>
        <span class="err-float err-float-2" aria-hidden="true">📊</span>
        <span class="err-float err-float-3" aria-hidden="true">🎯</span>
        <span class="err-float err-float-4" aria-hidden="true">💬</span>
        <span class="err-float err-float-5" aria-hidden="true">✅</span>

        <p class="err-404-num" aria-hidden="true">404</p>

        <div class="err-post-card">
            <div class="err-post-head">
                <div class="err-post-avatar" aria-hidden="true">?</div>
                <div class="err-post-meta">
                    <div class="err-post-name">Pulsify</div>
                    <div class="err-post-time">Just now</div>
                </div>
            </div>
            <div class="err-broken-media">
                <span class="warn" aria-hidden="true">⚠️</span>
                <span>This content couldn't be found</span>
            </div>
            <div class="err-caption">
                <span class="err-cursor" aria-hidden="true"></span>
            </div>
            <div class="err-engage">
                <span>❤️ 0</span>
                <span>💬 0</span>
                <span>📤 0</span>
            </div>
        </div>

        <h1 class="err-heading">Looks like this page got unpublished</h1>
        <p class="err-sub">The page you're looking for doesn't exist or has been moved. Let's get you back on track.</p>

        <div class="err-actions">
            <a href="{{ url('/dashboard') }}" class="btn-err-primary">← Back to Dashboard</a>
            <a href="{{ url('/') }}" class="btn-err-outline">Go to Home</a>
        </div>
    </div>
@endsection
