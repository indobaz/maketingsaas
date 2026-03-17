@php
    $user = auth()->user();
    $primaryColor = $primaryColor ?? ($user?->company?->primary_color ?? 'var(--color-primary)');

    $userName = (string) ($user?->name ?? 'User');
    $userWords = preg_split('/\s+/', trim($userName)) ?: [];
    $userInitials = strtoupper(collect($userWords)->filter()->take(2)->map(fn ($w) => mb_substr($w, 0, 1))->implode(''));
    $userInitials = $userInitials !== '' ? $userInitials : 'U';
@endphp

<div class="d-flex align-items-center w-100 justify-content-between">
    <div class="d-flex align-items-center gap-2">
        <button class="dash-icon-btn d-lg-none" type="button"
                data-bs-toggle="collapse" data-bs-target="#dashboardSidebar"
                aria-controls="dashboardSidebar" aria-expanded="false" aria-label="Toggle sidebar">
            <i class="bi bi-list" style="font-size: 18px;"></i>
        </button>
        <div style="font-size: 20px; font-weight: 600; color: #1C1C2E;">
            {{ $pageTitle ?? 'Dashboard' }}
        </div>
    </div>

    <div class="d-flex align-items-center gap-3">
        <button class="dash-icon-btn" type="button" aria-label="Search">
            <i class="bi bi-search"></i>
        </button>

        <button class="dash-icon-btn position-relative" type="button" aria-label="Notifications">
            <i class="bi bi-bell"></i>
            <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle" style="margin-left:-8px; margin-top: 6px;">
                <span class="visually-hidden">New alerts</span>
            </span>
        </button>

        <div style="width: 1px; height: 24px; background: #EAECF0;"></div>

        <div class="dropdown">
            <button class="btn p-0 border-0 bg-transparent d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <div style="width: 32px; height: 32px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-weight: 700; color: #fff; background: {{ $primaryColor }};">
                    {{ $userInitials }}
                </div>
                <div class="d-none d-md-block" style="font-size: 14px; color: #1C1C2E; font-weight: 600;">
                    {{ $userName }}
                    <i class="bi bi-chevron-down" style="font-size: 12px; margin-left: 6px; color: #6B7280;"></i>
                </div>
                <i class="bi bi-chevron-down d-md-none" style="font-size: 12px; color: #6B7280;"></i>
            </button>

            <ul class="dropdown-menu dropdown-menu-end" style="min-width: 220px;">
                <li><a class="dropdown-item" href="{{ url('/profile') }}">My Profile</a></li>
                <li><a class="dropdown-item" href="{{ url('/settings') }}">Settings</a></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form method="POST" action="{{ url('/logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item text-danger">Logout</button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</div>

