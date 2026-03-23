@extends('layouts.dashboard')

@php
    $pageTitle = 'Email Templates';
@endphp

@section('content')
    <p class="text-muted small mb-4">Customize emails sent to your team</p>

    @foreach($grouped as $category => $templates)
        <div class="mb-4">
            <h5 class="fw-semibold mb-3">{{ $category }}</h5>
            <div class="row g-3">
                @foreach($templates as $template)
                    <div class="col-12 col-md-6 col-xl-4">
                        <div class="card h-100 border-0 shadow-sm" style="border:1px solid #F0F2F5 !important; border-radius: 12px;">
                            <div class="card-body d-flex flex-column">
                                <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="{{ $template['icon'] }} text-primary"></i>
                                        <h6 class="mb-0">{{ $template['name'] }}</h6>
                                    </div>
                                    @if($template['is_custom'])
                                        <span class="badge bg-primary">Custom</span>
                                    @else
                                        <span class="badge bg-secondary">Default</span>
                                    @endif
                                </div>
                                <p class="text-muted small flex-grow-1 mb-3">{{ $template['description'] }}</p>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('email-templates.edit', $template['key']) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                    @if(strtolower((string) auth()->user()->role) === 'owner')
                                        <button type="button"
                                                class="btn btn-sm btn-outline-secondary template-test-btn"
                                                data-key="{{ $template['key'] }}">
                                            Send Test
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
@endsection

@section('scripts')
    <script>
        (function () {
            document.querySelectorAll('.template-test-btn').forEach(function (button) {
                button.addEventListener('click', function () {
                    var key = button.getAttribute('data-key');
                    if (!key) return;
                    button.disabled = true;

                    fetch('/email-templates/' + encodeURIComponent(key) + '/test', {
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
                        alert(data.message || (data.success ? 'Test email sent.' : 'Failed.'));
                    }).catch(function () {
                        alert('Network error while sending test email.');
                    }).finally(function () {
                        button.disabled = false;
                    });
                });
            });
        })();
    </script>
@endsection
