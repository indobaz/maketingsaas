@php
    $user = auth()->user();
    $company = $company ?? $user?->company;
    $primaryColor = $primaryColor ?? ($company?->primary_color ?? 'var(--color-primary)');

    $companyName = (string) ($company?->name ?? 'Company');
    $companyLogo = $company?->logo_url;
    $words = preg_split('/\s+/', trim($companyName)) ?: [];
    $companyInitials = strtoupper(collect($words)->filter()->take(2)->map(fn ($w) => mb_substr($w, 0, 1))->implode(''));
    $companyInitials = $companyInitials !== '' ? $companyInitials : 'C';

    $userName = (string) ($user?->name ?? 'User');
    $userWords = preg_split('/\s+/', trim($userName)) ?: [];
    $userInitials = strtoupper(collect($userWords)->filter()->take(2)->map(fn ($w) => mb_substr($w, 0, 1))->implode(''));
    $userInitials = $userInitials !== '' ? $userInitials : 'U';
    $userRole = (string) ($user?->role ?? 'member');

    $nav = [
        ['label' => 'Dashboard', 'icon' => 'bi-grid', 'href' => url('/dashboard'), 'active' => request()->is('dashboard')],
        ['label' => 'Channels', 'icon' => 'bi-broadcast', 'href' => url('/channels'), 'active' => request()->routeIs('channels.*')],
        ['label' => 'Content', 'icon' => 'bi-file-text', 'href' => url('/content'), 'active' => request()->is('content*')],
        ['label' => 'Calendar', 'icon' => 'bi-calendar3', 'href' => url('/calendar'), 'active' => request()->is('calendar*')],
        ['label' => 'Analytics', 'icon' => 'bi-bar-chart', 'href' => url('/analytics'), 'active' => request()->is('analytics*')],
        ['label' => 'Email Tracking', 'icon' => 'bi-envelope', 'href' => url('/email-tracking'), 'active' => request()->is('email-tracking*')],
        ['label' => 'Ads Tracking', 'icon' => 'bi-megaphone', 'href' => url('/ads-tracking'), 'active' => request()->is('ads-tracking*')],
        ['label' => 'Tasks', 'icon' => 'bi-check2-square', 'href' => url('/tasks'), 'active' => request()->is('tasks*')],
        ['label' => 'Campaigns', 'icon' => 'bi-bullseye', 'href' => url('/campaigns'), 'active' => request()->is('campaigns*')],
        ['label' => 'AI Reports', 'icon' => 'bi-robot', 'href' => url('/ai-reports'), 'active' => request()->is('ai-reports*')],
        ['label' => 'Competitors', 'icon' => 'bi-binoculars', 'href' => url('/competitors'), 'active' => request()->is('competitors*')],
    ];

    $settingsNav = [
        ['label' => 'Settings', 'icon' => 'bi-gear', 'href' => url('/settings'), 'active' => request()->is('settings*')],
        ['label' => 'Content Pillars', 'icon' => 'bi-columns-gap', 'href' => url('/pillars'), 'active' => request()->routeIs('pillars.*')],
        ['label' => 'Team', 'icon' => 'bi-people', 'href' => url('/team'), 'active' => request()->is('team*')],
    ];
@endphp

<style>
    .sidebar-brand {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 12px 16px;
    }

    .sidebar-brand .company-name {
        color: #fff;
        font-size: 14px;
        font-weight: 600;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 160px;
    }

    .sidebar-section-label {
        margin: 24px 16px 8px;
        font-size: 10px;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: rgba(255, 255, 255, 0.4);
    }

    .sidebar-nav a {
        padding: 10px 16px;
        border-radius: 8px;
        margin: 2px 8px;
        display: flex;
        align-items: center;
        gap: 10px;
        text-decoration: none;
        color: rgba(255, 255, 255, 0.65);
        transition: background-color .15s ease, color .15s ease;
        border-left: 3px solid transparent;
    }
    .sidebar-nav a:hover {
        background: rgba(255, 255, 255, 0.08);
        color: #fff;
    }
    .sidebar-nav a.is-active {
        background: rgba(255, 255, 255, 0.12);
        color: #fff;
    }

    .sidebar-bottom {
        margin-top: 18px;
        padding: 12px 12px 6px;
        border-top: 1px solid rgba(255, 255, 255, 0.12);
        display: flex;
        align-items: center;
        gap: 10px;
    }
</style>

<div class="d-flex flex-column h-100">
    <div class="d-flex align-items-center justify-content-between d-lg-none px-2" style="padding-bottom: 6px;">
        <div class="text-white" style="font-size: 12px; opacity: .85;">Menu</div>
        <button class="btn btn-sm text-white" type="button" data-bs-toggle="collapse" data-bs-target="#dashboardSidebar" aria-controls="dashboardSidebar" aria-expanded="true" aria-label="Close sidebar">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>

    <div class="sidebar-brand">
        @if($companyLogo)
            <img src="{{ $companyLogo }}" alt="{{ $companyName }} logo" style="width: 36px; height: 36px; border-radius: 10px; object-fit: cover;">
        @else
            <div style="width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-weight: 700; color: #fff; background: {{ $primaryColor }};">
                {{ $companyInitials }}
            </div>
        @endif
        <div class="company-name" title="{{ $companyName }}">{{ $companyName }}</div>
    </div>

    <div class="sidebar-section-label">MAIN MENU</div>
    <nav class="sidebar-nav">
        @foreach($nav as $item)
            <a href="{{ $item['href'] }}"
               class="{{ $item['active'] ? 'is-active' : '' }}"
               @if($item['active']) style="border-left-color:{{ $primaryColor }};" @endif>
                <i class="bi {{ $item['icon'] }}" style="font-size: 16px;"></i>
                <span style="font-size: 13px; font-weight: 500;">{{ $item['label'] }}</span>
            </a>
        @endforeach
    </nav>

    <div class="sidebar-section-label">SETTINGS</div>
    <nav class="sidebar-nav">
        @foreach($settingsNav as $item)
            <a href="{{ $item['href'] }}"
               class="{{ $item['active'] ? 'is-active' : '' }}"
               @if($item['active']) style="border-left-color:{{ $primaryColor }};" @endif>
                <i class="bi {{ $item['icon'] }}" style="font-size: 16px;"></i>
                <span style="font-size: 13px; font-weight: 500;">{{ $item['label'] }}</span>
            </a>
        @endforeach
    </nav>

    <div class="mt-auto sidebar-bottom">
        <div style="width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; color: #fff; background: {{ $primaryColor }};">
            {{ $userInitials }}
        </div>
        <div class="flex-grow-1" style="min-width: 0;">
            <div class="text-white" style="font-size: 13px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                {{ $userName }}
            </div>
            <div style="font-size: 11px; color: rgba(255,255,255,0.5); text-transform: capitalize;">
                {{ $userRole }}
            </div>
        </div>
        <span class="badge" style="background: rgba(255,255,255,0.18); color: rgba(255,255,255,0.9); font-size: 10px; font-weight: 600;">
            {{ strtoupper($userRole) }}
        </span>
    </div>
</div>

