@extends('onboarding.layout')

@section('content')
    @php
        $step = $step ?? 3;
        $stepName = $stepName ?? 'Brand Colors';
    @endphp

    <style>
        .color-row {
            display: grid;
            grid-template-columns: 1fr;
            gap: 14px;
        }

        @media (min-width: 768px) {
            .color-row {
                grid-template-columns: 1fr 1fr;
                gap: 14px;
            }
        }

        .color-field {
            border: 1px solid #EAECF0;
            border-radius: 12px;
            padding: 14px;
            transition: border-color var(--transition), box-shadow var(--transition);
        }

        .color-field label {
            font-weight: 500;
            font-size: 13px;
            color: #344054;
        }

        .color-inputs {
            display: grid;
            grid-template-columns: 44px 1fr 40px;
            gap: 10px;
            align-items: center;
            margin-top: 10px;
        }

        .hex-input {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
            letter-spacing: 0.2px;
        }

        .swatch {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            border: 1px solid #EAECF0;
            background: #fff;
        }

        .presets {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-top: 14px;
        }

        @media (min-width: 768px) {
            .presets {
                grid-template-columns: 1fr 1fr 1fr;
            }
        }

        .preset {
            border: 1px solid #EAECF0;
            border-radius: 10px;
            padding: 12px;
            background: #fff;
            cursor: pointer;
            user-select: none;
            transition: transform var(--transition), box-shadow var(--transition), border-color var(--transition);
        }

        .preset:hover {
            transform: translateY(-1px);
            border-color: rgba(95,99,242,0.35);
            box-shadow: 0 10px 24px rgba(16,24,40,0.08);
        }

        .preset:focus-visible {
            outline: none;
            box-shadow: 0 0 0 3px rgba(95,99,242,0.12);
            border-color: var(--color-primary);
            transform: translateY(-1px);
        }

        .preset-swatch {
            height: 34px;
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid #EAECF0;
            background: #fff;
            display: grid;
            grid-template-columns: 1fr 1fr;
        }

        .preset-meta {
            margin-top: 10px;
            display: grid;
            gap: 4px;
        }

        .preset-title {
            font-weight: 600;
            font-size: 13px;
            color: #101828;
        }

        .preset-codes {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
            font-size: 12px;
            color: #667085;
        }

        .preview-wrap {
            border: 1px solid #EAECF0;
            border-radius: 14px;
            overflow: hidden;
            margin-top: 16px;
        }

        .mini {
            display: grid;
            grid-template-columns: 180px 1fr;
            min-height: 170px;
        }

        .mini-sidebar {
            padding: 14px;
            background: #272B41;
            color: #fff;
            transition: background-color var(--transition);
        }

        .mini-brand {
            font-weight: 900;
            letter-spacing: .3px;
            font-size: 14px;
            opacity: 0.95;
        }

        .mini-nav {
            margin-top: 14px;
            display: grid;
            gap: 8px;
        }

        .mini-item {
            border-radius: 10px;
            padding: 10px 10px;
            font-weight: 700;
            font-size: 12px;
            background: rgba(255,255,255,0.10);
        }

        .mini-item.active {
            background: #5F63F2;
            color: #fff;
            transition: background-color var(--transition);
        }

        .mini-main {
            padding: 14px;
            background: #fff;
        }

        .mini-card {
            border: 1px solid #EAECF0;
            border-radius: 12px;
            padding: 12px;
        }

        .mini-badge {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            padding: 6px 10px;
            font-weight: 800;
            font-size: 12px;
            background: rgba(95,99,242,0.12);
            color: #5F63F2;
        }
    </style>

    <div class="step-kicker">
        Step 3 of 3
    </div>

    <div class="fw-semibold" style="font-size: 20px; letter-spacing: -0.2px;">
        Set your brand colors
    </div>
    <div class="text-muted mt-2" style="color: #667085 !important; font-size: 14px;">
        These colors will theme your Pulsify workspace
    </div>

    <form class="mt-4" method="POST" action="{{ url('/onboarding/step3') }}" id="brandForm" novalidate>
        @csrf

        <div class="color-row">
            <div class="color-field">
                <label class="form-label mb-0">Primary color</label>
                <div class="color-inputs">
                    <input class="form-control form-control-color" type="color" id="primaryPicker">
                    <input
                        class="form-control hex-input @error('primary_color') is-invalid @enderror"
                        type="text"
                        name="primary_color"
                        id="primaryHex"
                        value="{{ old('primary_color', $company?->primary_color ?? $defaultPrimary) }}"
                        placeholder="#5F63F2"
                        required
                    >
                    <div class="swatch" id="primarySwatch" aria-hidden="true"></div>
                </div>
                @error('primary_color')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="color-field">
                <label class="form-label mb-0">Secondary color</label>
                <div class="color-inputs">
                    <input class="form-control form-control-color" type="color" id="secondaryPicker">
                    <input
                        class="form-control hex-input @error('secondary_color') is-invalid @enderror"
                        type="text"
                        name="secondary_color"
                        id="secondaryHex"
                        value="{{ old('secondary_color', $company?->secondary_color ?? '#272B41') }}"
                        placeholder="#272B41"
                        required
                    >
                    <div class="swatch" id="secondarySwatch" aria-hidden="true"></div>
                </div>
                @error('secondary_color')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="mt-4">
            <div class="fw-semibold" style="font-size: 14px;">Presets</div>
            <div class="text-muted" style="color: #667085 !important; font-size: 13px;">Click a combo to auto-fill both colors.</div>

            <div class="presets">
                <div class="preset" data-primary="#5F63F2" data-secondary="#272B41" role="button" tabindex="0">
                    <div class="preset-swatch">
                        <div style="background:#5F63F2"></div>
                        <div style="background:#272B41"></div>
                    </div>
                    <div class="preset-meta">
                        <div class="preset-title">Professional Blue</div>
                        <div class="preset-codes">#5F63F2 · #272B41</div>
                    </div>
                </div>
                <div class="preset" data-primary="#20C997" data-secondary="#1A3C34" role="button" tabindex="0">
                    <div class="preset-swatch">
                        <div style="background:#20C997"></div>
                        <div style="background:#1A3C34"></div>
                    </div>
                    <div class="preset-meta">
                        <div class="preset-title">Forest Green</div>
                        <div class="preset-codes">#20C997 · #1A3C34</div>
                    </div>
                </div>
                <div class="preset" data-primary="#FA8B0C" data-secondary="#2D1F0A" role="button" tabindex="0">
                    <div class="preset-swatch">
                        <div style="background:#FA8B0C"></div>
                        <div style="background:#2D1F0A"></div>
                    </div>
                    <div class="preset-meta">
                        <div class="preset-title">Sunset Orange</div>
                        <div class="preset-codes">#FA8B0C · #2D1F0A</div>
                    </div>
                </div>
                <div class="preset" data-primary="#FF4D4F" data-secondary="#2D0A0A" role="button" tabindex="0">
                    <div class="preset-swatch">
                        <div style="background:#FF4D4F"></div>
                        <div style="background:#2D0A0A"></div>
                    </div>
                    <div class="preset-meta">
                        <div class="preset-title">Ruby Red</div>
                        <div class="preset-codes">#FF4D4F · #2D0A0A</div>
                    </div>
                </div>
                <div class="preset" data-primary="#9B59B6" data-secondary="#1A0A2D" role="button" tabindex="0">
                    <div class="preset-swatch">
                        <div style="background:#9B59B6"></div>
                        <div style="background:#1A0A2D"></div>
                    </div>
                    <div class="preset-meta">
                        <div class="preset-title">Royal Purple</div>
                        <div class="preset-codes">#9B59B6 · #1A0A2D</div>
                    </div>
                </div>
                <div class="preset" data-primary="#64748B" data-secondary="#1E293B" role="button" tabindex="0">
                    <div class="preset-swatch">
                        <div style="background:#64748B"></div>
                        <div style="background:#1E293B"></div>
                    </div>
                    <div class="preset-meta">
                        <div class="preset-title">Slate Gray</div>
                        <div class="preset-codes">#64748B · #1E293B</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="preview-wrap">
            <div class="mini">
                <aside class="mini-sidebar" id="previewSidebar">
                    <div class="mini-brand">Pulsify</div>
                    <div class="mini-nav">
                        <div class="mini-item active" id="previewActive">Dashboard</div>
                        <div class="mini-item">Calendar</div>
                        <div class="mini-item">Posts</div>
                    </div>
                </aside>
                <section class="mini-main">
                    <div class="mini-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="fw-bold">Preview</div>
                            <div class="mini-badge" id="previewBadge">Primary</div>
                        </div>
                        <div class="text-muted mt-2" style="color: var(--color-text-muted) !important; font-size: 13px;">
                            Sidebar uses secondary. Active item uses primary.
                        </div>
                    </div>
                </section>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-4">
            <a class="btn btn-outline-secondary" href="{{ url('/onboarding/step2') }}">
                &larr; Back
            </a>
            <button class="btn btn-primary btn-primary-soft" type="submit">
                Finish Setup
            </button>
        </div>
    </form>

@push('scripts')
    <script>
        (function () {
            function normalizeHex(input) {
                if (!input) return '';
                var v = String(input).trim();
                if (v[0] !== '#') v = '#' + v;
                v = v.toUpperCase();
                return v;
            }

            function isValidHex(v) {
                return /^#([A-F0-9]{6})$/.test(v);
            }

            var primaryHex = document.getElementById('primaryHex');
            var secondaryHex = document.getElementById('secondaryHex');
            var primaryPicker = document.getElementById('primaryPicker');
            var secondaryPicker = document.getElementById('secondaryPicker');
            var primarySwatch = document.getElementById('primarySwatch');
            var secondarySwatch = document.getElementById('secondarySwatch');
            var previewSidebar = document.getElementById('previewSidebar');
            var previewActive = document.getElementById('previewActive');
            var previewBadge = document.getElementById('previewBadge');

            function applyColors() {
                var p = normalizeHex(primaryHex.value);
                var s = normalizeHex(secondaryHex.value);

                if (isValidHex(p)) {
                    primaryHex.value = p;
                    primaryPicker.value = p;
                    primarySwatch.style.background = p;
                    previewActive.style.background = p;
                    previewBadge.style.background = 'rgba(0,0,0,0)';
                    previewBadge.style.color = p;
                    previewBadge.style.border = '1px solid rgba(39,43,65,0.10)';
                    previewBadge.style.boxShadow = '0 8px 24px rgba(95,99,242,0.12)';
                }

                if (isValidHex(s)) {
                    secondaryHex.value = s;
                    secondaryPicker.value = s;
                    secondarySwatch.style.background = s;
                    previewSidebar.style.background = s;
                }
            }

            function setBoth(p, s) {
                primaryHex.value = normalizeHex(p);
                secondaryHex.value = normalizeHex(s);
                applyColors();
            }

            primaryHex.addEventListener('input', applyColors);
            secondaryHex.addEventListener('input', applyColors);
            primaryPicker.addEventListener('input', function () {
                primaryHex.value = normalizeHex(primaryPicker.value);
                applyColors();
            });
            secondaryPicker.addEventListener('input', function () {
                secondaryHex.value = normalizeHex(secondaryPicker.value);
                applyColors();
            });

            document.querySelectorAll('.preset').forEach(function (el) {
                function activate() {
                    var p = el.getAttribute('data-primary');
                    var s = el.getAttribute('data-secondary');
                    setBoth(p, s);
                }
                el.addEventListener('click', activate);
                el.addEventListener('keydown', function (e) {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        activate();
                    }
                });
            });

            setBoth(primaryHex.value, secondaryHex.value);
        })();
    </script>
@endpush
@endsection

