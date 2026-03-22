@extends('layouts.dashboard')

@php
    $pageTitle = 'Settings';
    $primaryColor = $company->primary_color ?? '#5F63F2';
    $secondaryColor = $company->secondary_color ?? '#272B41';

    $planBadgeClass = match ($planKey) {
        'free' => 'bg-secondary',
        'starter' => 'bg-primary',
        'pro' => 'text-white',
        'enterprise' => 'bg-dark',
        default => 'bg-secondary',
    };
    $planBadgeStyle = $planKey === 'pro' ? 'background: linear-gradient(135deg, #6366F1, #8B5CF6);' : '';
@endphp

@section('content')
    <p class="text-muted small mb-3">Manage your workspace configuration</p>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-3 align-items-start">
        <div class="col-12 col-lg-3 col-xl-2">
            <nav class="nav flex-column gap-1 settings-nav" id="settingsNav" role="tablist" aria-label="Settings sections">
                <a class="nav-link settings-tab-link active rounded px-3 py-2" href="{{ route('settings.index', ['tab' => 'profile']) }}#profile" data-tab="profile">Company Profile</a>
                <a class="nav-link settings-tab-link rounded px-3 py-2" href="{{ route('settings.index', ['tab' => 'brand']) }}#brand" data-tab="brand">Brand Kit</a>
                <a class="nav-link settings-tab-link rounded px-3 py-2" href="{{ route('settings.index', ['tab' => 'team']) }}#team" data-tab="team">Team Members</a>
                <a class="nav-link settings-tab-link rounded px-3 py-2" href="{{ route('settings.index', ['tab' => 'pillars']) }}#pillars" data-tab="pillars">Content Pillars</a>
                <a class="nav-link settings-tab-link rounded px-3 py-2" href="{{ route('settings.index', ['tab' => 'email']) }}#email" data-tab="email">Email / SMTP</a>
                <a class="nav-link settings-tab-link rounded px-3 py-2" href="{{ route('settings.index', ['tab' => 'billing']) }}#billing" data-tab="billing">Billing &amp; Plan</a>
            </nav>
        </div>

        <div class="col-12 col-lg-9 col-xl-10">
            <div class="settings-tab-pane" id="tab-profile" data-tab-panel="profile" role="tabpanel">
                <div class="card border-0 shadow-sm" style="border-radius: 12px; border: 1px solid #F0F2F5 !important;">
                    <div class="card-body p-4">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
                            <h5 class="fw-semibold mb-0">Company Profile</h5>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge {{ $planBadgeClass }} px-3 py-2" style="{{ $planBadgeStyle }}">{{ ucfirst($planKey) }}</span>
                                <a href="#" class="btn btn-sm btn-outline-secondary settings-goto-tab" data-tab="billing">Upgrade Plan</a>
                            </div>
                        </div>

                        @if($isOwner)
                            <form method="POST" action="{{ route('settings.company') }}">
                                @csrf
                        @endif

                        <div class="mb-4 text-center text-lg-start">
                            @php
                                $logo = $company->logo_url;
                                $isImgLogo = $logo && (str_starts_with($logo, 'http') || str_starts_with($logo, 'data:image'));
                            @endphp
                            <div class="d-inline-flex flex-column align-items-center align-items-lg-start">
                                <div class="rounded-circle border d-flex align-items-center justify-content-center overflow-hidden bg-light" style="width: 96px; height: 96px;">
                                    @if($isImgLogo)
                                        <img src="{{ $logo }}" alt="" class="w-100 h-100" style="object-fit: cover;">
                                    @else
                                        <i class="bi bi-building text-muted" style="font-size: 2rem;"></i>
                                    @endif
                                </div>
                                <span class="small text-muted mt-2">Company logo</span>
                            </div>
                        </div>

                        @if($isOwner)
                            <div class="mb-3">
                                <label class="form-label">Logo URL</label>
                                <input type="text" name="logo_url" value="{{ old('logo_url', $company->logo_url && str_starts_with((string) $company->logo_url, 'http') ? $company->logo_url : '') }}"
                                       class="form-control @error('logo_url') is-invalid @enderror"
                                       placeholder="https://…" maxlength="2000">
                                <div class="form-text">Paste a URL to your logo image (Google Drive, Dropbox, CDN)</div>
                                @error('logo_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Company Name</label>
                                <input type="text" name="name" value="{{ old('name', $company->name) }}" required
                                       class="form-control @error('name') is-invalid @enderror" maxlength="255">
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Website URL</label>
                                <input type="url" name="website" value="{{ old('website', $company->website) }}"
                                       class="form-control @error('website') is-invalid @enderror" placeholder="https://example.com" maxlength="500">
                                @error('website')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Industry</label>
                                <select name="industry" class="form-select @error('industry') is-invalid @enderror">
                                    <option value="">—</option>
                                    @foreach($industries as $ind)
                                        <option value="{{ $ind }}" @selected(old('industry', $company->industry) === $ind)>{{ $ind }}</option>
                                    @endforeach
                                </select>
                                @error('industry')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Country</label>
                                <select name="country" class="form-select @error('country') is-invalid @enderror">
                                    <option value="">—</option>
                                    @foreach($countries as $c)
                                        <option value="{{ $c }}" @selected(old('country', $company->country) === $c)>{{ $c }}</option>
                                    @endforeach
                                </select>
                                @error('country')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="mb-4">
                                <label class="form-label">Timezone</label>
                                <select name="timezone" class="form-select @error('timezone') is-invalid @enderror">
                                    <option value="">—</option>
                                    @foreach($timezones as $tz)
                                        <option value="{{ $tz }}" @selected(old('timezone', $company->timezone) === $tz)>{{ $tz }}</option>
                                    @endforeach
                                </select>
                                @error('timezone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <button type="submit" class="btn text-white px-4" style="background: {{ $primaryColor }};">Save Changes</button>
                            </form>
                        @else
                            <p class="text-muted small">Only the workspace owner can edit company profile.</p>
                            <dl class="row small mb-0">
                                <dt class="col-sm-3 text-muted">Name</dt><dd class="col-sm-9">{{ $company->name }}</dd>
                                <dt class="col-sm-3 text-muted">Website</dt><dd class="col-sm-9">{{ $company->website ?? '—' }}</dd>
                                <dt class="col-sm-3 text-muted">Industry</dt><dd class="col-sm-9">{{ $company->industry ?? '—' }}</dd>
                                <dt class="col-sm-3 text-muted">Country</dt><dd class="col-sm-9">{{ $company->country ?? '—' }}</dd>
                                <dt class="col-sm-3 text-muted">Timezone</dt><dd class="col-sm-9">{{ $company->timezone ?? '—' }}</dd>
                            </dl>
                        @endif
                    </div>
                </div>
            </div>

            <div class="settings-tab-pane d-none" id="tab-brand" data-tab-panel="brand" role="tabpanel">
                <div class="card border-0 shadow-sm" style="border-radius: 12px; border: 1px solid #F0F2F5 !important;">
                    <div class="card-body p-4">
                        <h5 class="fw-semibold mb-3">Brand Colors</h5>

                        @if($isOwner)
                            @include('settings.partials.brand-kit-form', [
                                'company' => $company,
                                'defaultPrimary' => $defaultPrimary,
                                'primaryColor' => $primaryColor,
                            ])
                            <p class="text-muted small mt-3 mb-0">Changes apply immediately for all team members</p>
                        @else
                            <p class="text-muted small">Only the workspace owner can update brand colors.</p>
                            <div class="d-flex gap-2 align-items-center">
                                <span class="rounded border" style="width:40px;height:40px;background:{{ $company->primary_color }}"></span>
                                <code>{{ $company->primary_color }}</code>
                                <span class="text-muted mx-2">/</span>
                                <span class="rounded border" style="width:40px;height:40px;background:{{ $company->secondary_color }}"></span>
                                <code>{{ $company->secondary_color }}</code>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="settings-tab-pane d-none" id="tab-team" data-tab-panel="team" role="tabpanel">
                <div class="card border-0 shadow-sm" style="border-radius: 12px; border: 1px solid #F0F2F5 !important;">
                    <div class="card-body p-4">
                        <h5 class="fw-semibold mb-2">Team Members</h5>
                        <p class="text-muted">Team management is on the Team page.</p>
                        <a href="{{ url('/team') }}" class="btn btn-outline-primary">Go to Team →</a>
                    </div>
                </div>
            </div>

            <div class="settings-tab-pane d-none" id="tab-pillars" data-tab-panel="pillars" role="tabpanel">
                <div class="card border-0 shadow-sm" style="border-radius: 12px; border: 1px solid #F0F2F5 !important;">
                    <div class="card-body p-4">
                        <h5 class="fw-semibold mb-2">Content Pillars</h5>
                        <p class="text-muted mb-1">Content pillars are managed on the Pillars page.</p>
                        <p class="small text-muted">You have {{ $contentPillars->count() }} pillar(s) configured.</p>
                        <a href="{{ route('pillars.index') }}" class="btn btn-outline-primary">Go to Content Pillars →</a>
                    </div>
                </div>
            </div>

            <div class="settings-tab-pane d-none" id="tab-email" data-tab-panel="email" role="tabpanel">
                <div class="card border-0 shadow-sm" style="border-radius: 12px; border: 1px solid #F0F2F5 !important;">
                    <div class="card-body p-4">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                            <div>
                                <h5 class="fw-semibold mb-1">Email Configuration</h5>
                                <p class="text-muted small mb-0">Configure SMTP to send invite emails, OTP codes, and notifications from your own email address</p>
                            </div>
                            @if($smtpConfigured)
                                <span class="badge bg-success">Configured</span>
                            @else
                                <span class="badge bg-warning text-dark">Not configured</span>
                            @endif
                        </div>

                        @if($isOwner)
                            <form method="POST" action="{{ route('settings.smtp') }}" id="smtpForm">
                                @csrf
                                <div class="row g-3">
                                    <div class="col-md-8">
                                        <label class="form-label">SMTP Host</label>
                                        <input type="text" name="smtp_host" class="form-control @error('smtp_host') is-invalid @enderror"
                                               value="{{ old('smtp_host', $smtpForm['host'] ?? '') }}" placeholder="smtp.gmail.com" required>
                                        @error('smtp_host')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">SMTP Port</label>
                                        <input type="number" name="smtp_port" class="form-control @error('smtp_port') is-invalid @enderror"
                                               value="{{ old('smtp_port', $smtpForm['port'] ?? 587) }}" placeholder="587" required>
                                        @error('smtp_port')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">SMTP Username</label>
                                        <input type="text" name="smtp_username" class="form-control @error('smtp_username') is-invalid @enderror"
                                               value="{{ old('smtp_username', $smtpForm['username'] ?? '') }}" placeholder="your@email.com" required>
                                        @error('smtp_username')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">SMTP Password</label>
                                        <input type="password" name="smtp_password" class="form-control @error('smtp_password') is-invalid @enderror"
                                               placeholder="{{ $smtpConfigured ? 'Enter password to update' : '' }}" required autocomplete="new-password">
                                        @error('smtp_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">From Email</label>
                                        <input type="email" name="smtp_from_email" class="form-control @error('smtp_from_email') is-invalid @enderror"
                                               value="{{ old('smtp_from_email', $smtpForm['from_email'] ?? '') }}" placeholder="noreply@yourcompany.com" required>
                                        @error('smtp_from_email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">From Name</label>
                                        <input type="text" name="smtp_from_name" class="form-control @error('smtp_from_name') is-invalid @enderror"
                                               value="{{ old('smtp_from_name', $smtpForm['from_name'] ?? '') }}" placeholder="Your Company Name" required>
                                        @error('smtp_from_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="d-flex flex-wrap gap-2 mt-4">
                                    <button type="submit" class="btn text-white" style="background: {{ $primaryColor }};">Save Email Settings</button>
                                    <button type="button" class="btn btn-outline-secondary" id="smtpTestBtn">Send Test Email</button>
                                </div>
                            </form>
                            <div class="small mt-3 d-none" id="smtpTestResult" role="status"></div>
                        @else
                            <p class="text-muted small">Only the workspace owner can configure SMTP.</p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="settings-tab-pane d-none" id="tab-billing" data-tab-panel="billing" role="tabpanel">
                <div class="card border-0 shadow-sm" style="border-radius: 12px; border: 1px solid #F0F2F5 !important;">
                    <div class="card-body p-4">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
                            <div>
                                <h5 class="fw-semibold mb-2">Billing &amp; Plan</h5>
                                <span class="badge {{ $planBadgeClass }} px-3 py-2 fs-6" style="{{ $planBadgeStyle }}">{{ ucfirst($planKey) }}</span>
                            </div>
                            <button type="button" class="btn btn-secondary" disabled title="Coming soon">Upgrade Plan</button>
                        </div>

                        <div class="table-responsive mb-4">
                            <table class="table table-bordered align-middle small">
                                <thead class="table-light">
                                    <tr>
                                        <th>Plan</th>
                                        <th>Users</th>
                                        <th>Channels</th>
                                        <th>Posts</th>
                                        <th>AI</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="{{ $planKey === 'free' ? 'table-primary' : '' }}">
                                        <td class="fw-semibold">Free</td>
                                        <td>1 user</td>
                                        <td>2 channels</td>
                                        <td>30 posts/month</td>
                                        <td>No AI</td>
                                    </tr>
                                    <tr class="{{ $planKey === 'starter' ? 'table-primary' : '' }}">
                                        <td class="fw-semibold">Starter</td>
                                        <td>3 users</td>
                                        <td>5 channels</td>
                                        <td>Unlimited posts</td>
                                        <td>Basic AI</td>
                                    </tr>
                                    <tr class="{{ $planKey === 'pro' ? 'table-primary' : '' }}">
                                        <td class="fw-semibold">Pro</td>
                                        <td>10 users</td>
                                        <td>Unlimited channels</td>
                                        <td>Unlimited posts</td>
                                        <td>Full AI + all modules</td>
                                    </tr>
                                    <tr class="{{ $planKey === 'enterprise' ? 'table-primary' : '' }}">
                                        <td class="fw-semibold">Enterprise</td>
                                        <td colspan="4">Custom</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <h6 class="fw-semibold mb-2">Usage</h6>
                        <ul class="list-unstyled small text-muted mb-0">
                            <li class="mb-1">
                                <strong class="text-dark">Members</strong>
                                (including invites):
                                {{ $teamMembers->count() }}
                                /
                                @if($planLimits['users'] === null)
                                    Custom
                                @else
                                    {{ $planLimits['users'] }}
                                @endif
                            </li>
                            <li>
                                <strong class="text-dark">Channels</strong>:
                                {{ $channelCount }}
                                /
                                @if($planLimits['channels'] === null)
                                    Unlimited
                                @else
                                    {{ $planLimits['channels'] }}
                                @endif
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .settings-nav .nav-link {
            color: #64748b;
            font-weight: 500;
            font-size: 14px;
            border: 1px solid transparent;
        }
        .settings-nav .nav-link:hover {
            background: rgba(95, 99, 242, 0.06);
            color: #272b41;
        }
        .settings-nav .nav-link.active {
            background: rgba(95, 99, 242, 0.12);
            color: {{ $primaryColor }};
            border-color: rgba(95, 99, 242, 0.2);
        }
    </style>
@endsection

@section('scripts')
    @include('settings.partials.brand-kit-scripts')
    <script>
        (function () {
            var valid = ['profile', 'brand', 'team', 'pillars', 'email', 'billing'];

            function parseTab() {
                var params = new URLSearchParams(window.location.search);
                var q = params.get('tab');
                if (q && valid.indexOf(q) !== -1) return q;
                var h = (window.location.hash || '').replace(/^#/, '');
                if (h && valid.indexOf(h) !== -1) return h;
                return 'profile';
            }

            function setTab(tab, opts) {
                opts = opts || {};
                document.querySelectorAll('.settings-tab-pane').forEach(function (el) {
                    var t = el.getAttribute('data-tab-panel');
                    if (t === tab) {
                        el.classList.remove('d-none');
                    } else {
                        el.classList.add('d-none');
                    }
                });
                document.querySelectorAll('.settings-tab-link').forEach(function (a) {
                    if (a.getAttribute('data-tab') === tab) {
                        a.classList.add('active');
                    } else {
                        a.classList.remove('active');
                    }
                });
                if (!opts.skipHistory) {
                    var url = '{{ url('/settings') }}?tab=' + encodeURIComponent(tab) + '#' + tab;
                    if (window.history && window.history.replaceState) {
                        window.history.replaceState(null, '', url);
                    } else {
                        window.location.hash = tab;
                    }
                }
            }

            document.querySelectorAll('.settings-tab-link').forEach(function (a) {
                a.addEventListener('click', function (e) {
                    e.preventDefault();
                    var tab = a.getAttribute('data-tab');
                    if (!tab) return;
                    setTab(tab);
                });
            });

            document.querySelectorAll('.settings-goto-tab').forEach(function (btn) {
                btn.addEventListener('click', function (e) {
                    e.preventDefault();
                    var tab = btn.getAttribute('data-tab');
                    if (tab) setTab(tab);
                });
            });

            setTab(parseTab(), { skipHistory: true });

            window.addEventListener('hashchange', function () {
                var t = parseTab();
                setTab(t, { skipHistory: true });
            });

            var testBtn = document.getElementById('smtpTestBtn');
            var testResult = document.getElementById('smtpTestResult');
            if (testBtn && testResult) {
                testBtn.addEventListener('click', function () {
                    testBtn.disabled = true;
                    testResult.classList.remove('d-none', 'text-success', 'text-danger');
                    testResult.textContent = 'Sending…';
                    testResult.classList.add('text-muted');
                    fetch('{{ route('settings.smtp.test') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({})
                    }).then(function (r) { return r.json(); }).then(function (data) {
                        testResult.classList.remove('text-muted');
                        if (data.success) {
                            testResult.classList.add('text-success');
                            testResult.textContent = data.message || 'Sent.';
                        } else {
                            testResult.classList.add('text-danger');
                            testResult.textContent = data.message || 'Failed.';
                        }
                    }).catch(function () {
                        testResult.classList.remove('text-muted');
                        testResult.classList.add('text-danger');
                        testResult.textContent = 'Network error.';
                    }).finally(function () {
                        testBtn.disabled = false;
                    });
                });
            }
        })();
    </script>
@endsection
