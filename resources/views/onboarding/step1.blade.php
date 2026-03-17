@extends('onboarding.layout')

@section('content')
    @php
        $step = $step ?? 1;
        $stepName = $stepName ?? 'Company Details';
    @endphp

    <div class="step-kicker">
        Step 1 of 3
    </div>

    <div class="fw-semibold" style="font-size: 20px; letter-spacing: -0.2px;">
        Tell us about your company
    </div>
    <div class="text-muted mt-2" style="color: #667085 !important; font-size: 14px;">
        This helps us personalize your workspace.
    </div>

    <form class="mt-4" method="POST" action="{{ url('/onboarding/step1') }}">
        @csrf

        <div class="text-center mb-4">
            <input type="file" id="logoFile" accept="image/*" class="d-none">
            <input type="hidden" name="logo_data" id="logoData" value="{{ old('logo_data') }}">

            <button
                type="button"
                id="logoTrigger"
                class="d-inline-flex flex-column align-items-center justify-content-center"
                style="
                    width: 110px;
                    background: transparent;
                    border: 0;
                    padding: 0;
                    transition: transform var(--transition);
                "
            >
                <div
                    id="logoCircle"
                    style="
                        width: 80px;
                        height: 80px;
                        border-radius: 999px;
                        border: 1.5px dashed #D0D5DD;
                        background: #fff;
                        display: grid;
                        place-items: center;
                        overflow: hidden;
                        transition: border-color var(--transition), box-shadow var(--transition);
                    "
                >
                    <img id="logoPreview" alt="" style="display:none; width:100%; height:100%; object-fit:cover;">
                    <i id="logoIcon" class="bi bi-building" style="font-size: 24px; color: #98A2B3;"></i>
                </div>
                <div class="mt-2" style="font-size: 12px; color: #667085;">
                    Add company logo
                </div>
            </button>
        </div>

        <div class="mb-3">
            <label class="form-label">Company Name</label>
            <input
                type="text"
                name="name"
                value="{{ old('name', $company?->name) }}"
                class="form-control @error('name') is-invalid @enderror"
                placeholder="e.g. Acme Trading LLC"
                required
                autofocus
            >
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Website URL <span class="text-muted fw-normal">(optional)</span></label>
            <input
                type="url"
                name="website"
                value="{{ old('website', $company?->website) }}"
                class="form-control @error('website') is-invalid @enderror"
                placeholder="https://example.com"
            >
            @error('website')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="d-grid mt-4">
            <button class="btn btn-primary btn-primary-soft" type="submit">
                Continue &rarr;
            </button>
        </div>
    </form>

    @push('scripts')
        <script>
            (function () {
                var trigger = document.getElementById('logoTrigger');
                var file = document.getElementById('logoFile');
                var data = document.getElementById('logoData');
                var img = document.getElementById('logoPreview');
                var icon = document.getElementById('logoIcon');
                var circle = document.getElementById('logoCircle');

                function setPreview(base64) {
                    if (!base64) return;
                    data.value = base64;
                    img.src = base64;
                    img.style.display = 'block';
                    icon.style.display = 'none';
                    circle.style.borderStyle = 'solid';
                    circle.style.borderColor = 'rgba(95,99,242,0.35)';
                    circle.style.boxShadow = '0 0 0 3px rgba(95,99,242,0.12)';
                }

                trigger.addEventListener('click', function () {
                    file.click();
                });

                trigger.addEventListener('mouseover', function () {
                    trigger.style.transform = 'translateY(-1px)';
                });
                trigger.addEventListener('mouseout', function () {
                    trigger.style.transform = 'translateY(0)';
                });

                file.addEventListener('change', function () {
                    var f = file.files && file.files[0];
                    if (!f) return;
                    if (!f.type || f.type.indexOf('image/') !== 0) return;

                    var reader = new FileReader();
                    reader.onload = function (e) {
                        setPreview(e.target && e.target.result ? String(e.target.result) : '');
                    };
                    reader.readAsDataURL(f);
                });

                if (data.value) {
                    setPreview(data.value);
                }
            })();
        </script>
    @endpush
@endsection

