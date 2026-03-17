@extends('onboarding.layout')

@section('content')
    @php
        $step = $step ?? 2;
        $stepName = $stepName ?? 'Your Business';
    @endphp

    <style>
        .select-wrap {
            position: relative;
        }

        .select-wrap .form-select {
            appearance: none;
            padding-right: 40px;
            background-image: none;
        }

        .select-wrap .chevron {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #98A2B3;
            pointer-events: none;
            transition: color var(--transition);
        }

        .select-wrap:focus-within .chevron {
            color: var(--color-primary);
        }

        .helper {
            margin-top: 8px;
            font-size: 12px;
            color: #667085;
        }
    </style>

    <div class="step-kicker">
        Step 2 of 3
    </div>

    <div class="fw-semibold" style="font-size: 20px; letter-spacing: -0.2px;">
        About your business
    </div>
    <div class="text-muted mt-2" style="color: #667085 !important; font-size: 14px;">
        Tell us where you operate so we can set things up correctly.
    </div>

    <form class="mt-4" method="POST" action="{{ url('/onboarding/step2') }}">
        @csrf

        <div class="mb-3">
            <label class="form-label">Industry</label>
            <div class="select-wrap">
                <select name="industry" class="form-select @error('industry') is-invalid @enderror" required>
                    <option value="" disabled {{ old('industry', $company?->industry) ? '' : 'selected' }}>Select an industry</option>
                    @foreach ($industries as $industry)
                        <option value="{{ $industry }}" {{ old('industry', $company?->industry) === $industry ? 'selected' : '' }}>
                            {{ $industry }}
                        </option>
                    @endforeach
                </select>
                <i class="bi bi-chevron-down chevron" aria-hidden="true"></i>
            </div>
            @error('industry')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Country</label>
            <div class="select-wrap">
                <select name="country" class="form-select @error('country') is-invalid @enderror" required>
                    <option value="" disabled {{ old('country', $company?->country) ? '' : 'selected' }}>Select a country</option>
                    @foreach ($countries as $country)
                        <option value="{{ $country }}" {{ old('country', $company?->country) === $country ? 'selected' : '' }}>
                            {{ $country }}
                        </option>
                    @endforeach
                </select>
                <i class="bi bi-chevron-down chevron" aria-hidden="true"></i>
            </div>
            @error('country')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Timezone</label>
            <div class="select-wrap">
                <select name="timezone" class="form-select @error('timezone') is-invalid @enderror" required>
                    @foreach ($timezones as $tz)
                        <option value="{{ $tz }}" {{ old('timezone', $company?->timezone ?? 'Asia/Dubai') === $tz ? 'selected' : '' }}>
                            {{ $tz }}
                        </option>
                    @endforeach
                </select>
                <i class="bi bi-chevron-down chevron" aria-hidden="true"></i>
            </div>
            @error('timezone')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <div class="helper">
                Used to schedule your AI reports and calendar events
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-4">
            <a class="btn btn-outline-secondary" href="{{ url('/onboarding/step1') }}">
                &larr; Back
            </a>
            <button class="btn btn-primary btn-primary-soft" type="submit">
                Continue &rarr;
            </button>
        </div>
    </form>
@endsection

