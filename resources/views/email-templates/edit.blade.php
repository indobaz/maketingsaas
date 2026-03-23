@extends('layouts.dashboard')

@php
    $pageTitle = 'Edit Email Template';
@endphp

@section('content')
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h5 class="fw-semibold mb-1">{{ $template->name }}</h5>
            <div class="small text-muted">
                Template key: <code>{{ $templateKey }}</code> •
                @if($isCustom)
                    <span class="badge bg-primary">Custom</span>
                @else
                    <span class="badge bg-secondary">Default</span>
                @endif
            </div>
        </div>
        <a href="{{ route('email-templates.index') }}" class="btn btn-outline-secondary btn-sm">Back</a>
    </div>

    <div class="row g-3">
        <div class="col-12 col-xl-7">
            <div class="card border-0 shadow-sm" style="border:1px solid #F0F2F5 !important;">
                <div class="card-body">
                    <form method="POST" action="{{ route('email-templates.update', $templateKey) }}" id="templateEditForm">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Subject</label>
                            <input type="text" class="form-control @error('subject') is-invalid @enderror"
                                   name="subject" id="subjectInput" value="{{ old('subject', $template->subject) }}" maxlength="500" required>
                            @error('subject')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">HTML Body</label>
                            <textarea class="form-control font-monospace @error('body_html') is-invalid @enderror"
                                      name="body_html" id="bodyInput" rows="16" style="min-height: 400px;" required>{{ old('body_html', $template->body_html) }}</textarea>
                            @error('body_html')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="d-flex flex-wrap gap-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <button type="button" class="btn btn-outline-secondary" id="sendTestBtn">Send Test</button>
                        </div>
                    </form>

                    <form method="POST" action="{{ route('email-templates.reset', $templateKey) }}" class="mt-2"
                          onsubmit="return confirm('Reset this template to system default?');">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger btn-sm">Reset to Default</button>
                    </form>

                    <div class="mt-3 p-3 border rounded bg-light">
                        <h6 class="mb-2">Available variables</h6>
                        <div class="small">
                            @foreach($variableDescriptions as $var => $desc)
                                <div><code>{{ '{' }}{{ '{' }}{{ $var }}{{ '}' }}{{ '}' }}</code> — {{ $desc }}</div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-5">
            <div class="card border-0 shadow-sm" style="border:1px solid #F0F2F5 !important;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0">Live Preview</h6>
                        <a id="openPreviewLink" href="#" target="_blank" rel="noopener" class="small">Open in new tab</a>
                    </div>
                    <iframe id="previewFrame" style="width:100%; min-height:580px; border:1px solid #e5e7eb; border-radius:8px;"></iframe>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        (function () {
            var subjectInput = document.getElementById('subjectInput');
            var bodyInput = document.getElementById('bodyInput');
            var frame = document.getElementById('previewFrame');
            var openLink = document.getElementById('openPreviewLink');
            var sendTestBtn = document.getElementById('sendTestBtn');
            var timer = null;

            function previewUrl() {
                var base = '{{ route('email-templates.preview', $templateKey) }}';
                var url = new URL(base, window.location.origin);
                url.searchParams.set('subject', subjectInput.value || '');
                url.searchParams.set('body_html', bodyInput.value || '');
                return url.toString();
            }

            function updatePreview() {
                var url = previewUrl();
                frame.src = url;
                openLink.href = url;
            }

            function debouncedPreview() {
                clearTimeout(timer);
                timer = setTimeout(updatePreview, 500);
            }

            [subjectInput, bodyInput].forEach(function (el) {
                el.addEventListener('keyup', debouncedPreview);
                el.addEventListener('change', debouncedPreview);
            });

            if (sendTestBtn) {
                sendTestBtn.addEventListener('click', function () {
                    sendTestBtn.disabled = true;
                    fetch('{{ route('email-templates.test', $templateKey) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({})
                    }).then(function (res) {
                        return res.json();
                    }).then(function (data) {
                        alert(data.message || (data.success ? 'Sent.' : 'Failed.'));
                    }).catch(function () {
                        alert('Network error while sending test email.');
                    }).finally(function () {
                        sendTestBtn.disabled = false;
                    });
                });
            }

            updatePreview();
        })();
    </script>
@endsection
