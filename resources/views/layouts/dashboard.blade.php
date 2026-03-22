<!DOCTYPE html>
<html lang="en">
<head>
    @php
        $user = auth()->user();
        $company = $user?->company;
        $primaryColor = $company?->primary_color ?? '#5F63F2';

        $title = $pageTitle ?? 'Dashboard';
        $subTitle = $company?->name ?? 'Pulsify';
    @endphp

    @include('layouts.partials/title-meta', ['title' => $title])
    @include('layouts.partials/head-css')

    <style>
        :root {
            --pulsify-accent: {{ $primaryColor }};
            --brand-primary: {{ $company?->primary_color ?? '#5F63F2' }};
            --brand-secondary: {{ $company?->secondary_color ?? '#272B41' }};
        }
        .text-pulsify-accent { color: var(--pulsify-accent) !important; }
        .bg-pulsify-accent { background-color: var(--pulsify-accent) !important; }
        .border-pulsify-accent { border-color: var(--pulsify-accent) !important; }

        .pulsify-brand {
            padding: 20px 16px;
            display: flex;
            flex-direction: column;
            gap: 4px;
            text-decoration: none;
            min-width: 0;
        }
        .pulsify-brand .brand-expanded { display: block; }
        .pulsify-brand .brand-collapsed { display: none; }

        html[data-menu-size="condensed"] .pulsify-brand {
            padding: 20px 16px;
            align-items: center;
        }
        html[data-menu-size="condensed"] .pulsify-brand .brand-expanded { display: none; }
        html[data-menu-size="condensed"] .pulsify-brand .brand-collapsed { display: flex; justify-content: center; width: 100%; }

    </style>
</head>

<body>
<div class="wrapper">

    <header class="">
        <div class="topbar">
            <div class="container-fluid">
                <div class="navbar-header d-flex align-items-center flex-wrap justify-content-between gap-2">
                    <div class="d-flex align-items-center gap-2 flex-grow-1 min-w-0">
                        <div class="topbar-item">
                            <button type="button" class="button-toggle-menu topbar-button">
                                <i class="ri-menu-2-line fs-24"></i>
                            </button>
                        </div>

                        <form class="app-search d-none d-md-block me-auto">
                            <div class="position-relative">
                                <input type="search" class="form-control border-0" placeholder="Search..." autocomplete="off">
                                <i class="ri-search-line search-widget-icon"></i>
                            </div>
                        </form>
                        <a href="{{ url('/content/create') }}" style="margin-left:12px; padding:6px 16px; background:var(--brand-primary,#5F63F2); color:#fff; border-radius:8px; font-size:13px; font-weight:500; white-space:nowrap; display:inline-flex; align-items:center; gap:5px; text-decoration:none; line-height:1.5; border:none;">
                            + New Post
                        </a>
                    </div>

                    <div class="d-flex align-items-center gap-1">
                        <div class="topbar-item">
                            <button type="button" class="topbar-button" id="light-dark-mode">
                                <i class="ri-moon-line fs-24 light-mode"></i>
                                <i class="ri-sun-line fs-24 dark-mode"></i>
                            </button>
                        </div>

                        <div class="dropdown topbar-item d-none d-lg-flex">
                            <button type="button" class="topbar-button" data-toggle="fullscreen">
                                <i class="ri-fullscreen-line fs-24 fullscreen"></i>
                                <i class="ri-fullscreen-exit-line fs-24 quit-fullscreen"></i>
                            </button>
                        </div>

                        <div class="topbar-item d-none d-md-flex">
                            <button type="button" class="topbar-button" id="theme-settings-btn" data-bs-toggle="offcanvas" data-bs-target="#theme-settings-offcanvas" aria-controls="theme-settings-offcanvas">
                                <i class="ri-settings-4-line fs-24"></i>
                            </button>
                        </div>

                        <div class="dropdown topbar-item">
                            <a type="button" class="topbar-button" id="page-header-user-dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="d-flex align-items-center">
                                    <span class="avatar-sm">
                                        <span class="avatar-title rounded-circle bg-pulsify-accent text-white fw-bold">
                                            {{ strtoupper(mb_substr((string) ($user?->name ?? 'U'), 0, 1)) }}
                                        </span>
                                    </span>
                                </span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end">
                                <div class="px-3 py-2">
                                    <div class="fw-semibold">{{ $user?->name }}</div>
                                    <div class="text-muted small">{{ $user?->role }}</div>
                                    <div class="text-muted small">{{ $company?->name }}</div>
                                </div>
                                <div class="dropdown-divider my-1"></div>
                                <form method="POST" action="{{ url('/logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">
                                        <iconify-icon icon="solar:logout-3-broken" class="align-middle me-2 fs-18"></iconify-icon>
                                        <span class="align-middle">Logout</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="main-nav" style="background-color: {{ auth()->user()->company->secondary_color }};">
        <div class="logo-box">
            <a href="{{ url('/dashboard') }}" class="pulsify-brand">
                <span class="brand-expanded">
                    <div style="display:flex; flex-direction:column; gap:1px; min-width:0;">
                        <span class="text-white" style="font-size:15px; font-weight:700; color:#fff; line-height:1.2; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                            {{ auth()->user()->company->name }}
                        </span>
                        <span style="font-size:10px; color:rgba(255,255,255,0.45); line-height:1.2; margin-top:0;">
                            Powered by Pulsify
                        </span>
                    </div>
                </span>

                <span class="brand-collapsed">
                    <span style="width: 36px; height: 36px; min-width: 36px; min-height: 36px; border-radius: 8px; background: var(--brand-primary, #5F63F2); color: #fff; font-size: 16px; font-weight: 700; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                        {{ strtoupper(substr(auth()->user()->company->name, 0, 1)) }}
                    </span>
                </span>
            </a>
        </div>

        <button type="button" class="button-sm-hover" aria-label="Show Full Sidebar">
            <i class="ri-menu-2-line fs-24 button-sm-hover-icon"></i>
        </button>

        <div class="scrollbar" data-simplebar>
            <ul class="navbar-nav" id="navbar-nav">
                <li class="menu-title">Menu</li>

                @php
                    $navItems = [
                        ['label' => 'Dashboard', 'href' => url('/dashboard'), 'icon' => 'ri-dashboard-line', 'placeholder' => false, 'active' => request()->is('dashboard')],
                        ['label' => 'Channels', 'href' => url('/channels'), 'icon' => 'ri-broadcast-line', 'placeholder' => false, 'active' => request()->is('channels*')],
                        ['label' => 'Content', 'href' => url('/content'), 'icon' => 'ri-file-text-line', 'placeholder' => false, 'active' => request()->is('content*')],
                        ['label' => 'Calendar', 'href' => url('/calendar'), 'icon' => 'ri-calendar-line', 'placeholder' => false, 'active' => request()->is('calendar*')],
                        ['label' => 'Analytics', 'href' => 'javascript:void(0)', 'icon' => 'ri-bar-chart-2-line', 'placeholder' => true, 'active' => false],
                        ['label' => 'Email Tracking', 'href' => 'javascript:void(0)', 'icon' => 'ri-mail-line', 'placeholder' => true, 'active' => false],
                        ['label' => 'Ads Tracking', 'href' => 'javascript:void(0)', 'icon' => 'ri-megaphone-line', 'placeholder' => true, 'active' => false],
                        ['label' => 'Tasks', 'href' => url('/tasks'), 'icon' => 'ri-checkbox-multiple-line', 'placeholder' => false, 'active' => request()->is('tasks*')],
                        ['label' => 'Campaigns', 'href' => 'javascript:void(0)', 'icon' => 'ri-focus-3-line', 'placeholder' => true, 'active' => false],
                        ['label' => 'AI Reports', 'href' => 'javascript:void(0)', 'icon' => 'ri-robot-line', 'placeholder' => true, 'active' => false],
                        ['label' => 'Competitors', 'href' => 'javascript:void(0)', 'icon' => 'ri-spy-line', 'placeholder' => true, 'active' => false],
                        ['label' => 'Settings', 'href' => url('/settings'), 'icon' => 'ri-settings-3-line', 'placeholder' => false, 'active' => request()->is('settings*')],
                        ['label' => 'Team', 'href' => url('/team'), 'icon' => 'ri-team-line', 'placeholder' => false, 'active' => request()->is('team*')],
                    ];
                    $navPrimary = auth()->user()->company->primary_color;
                @endphp

                @foreach($navItems as $item)
                    @php
                        $isActive = $item['active'];
                        $isPlaceholder = $item['placeholder'];
                        $navStyle = '';
                        if ($isActive && ! $isPlaceholder) {
                            $navStyle = 'color: '.$navPrimary.'; border-left: 3px solid '.$navPrimary.';';
                        }
                        if ($isPlaceholder) {
                            $navStyle .= ($navStyle !== '' ? ' ' : '').'opacity: 0.6; cursor: default;';
                        }
                    @endphp
                    <li class="nav-item">
                        <a class="nav-link {{ $isActive ? 'active' : '' }}"
                           href="{{ $item['href'] }}"
                           @if($isPlaceholder) title="Coming Soon" @endif
                           @if($navStyle !== '') style="{{ $navStyle }}" @endif>
                            <span class="nav-icon"><i class="{{ $item['icon'] }}"></i></span>
                            <span class="nav-text" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; font-size: 13px;">
                                {{ $item['label'] }}
                            </span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    <div class="page-content">
        <div class="container-fluid">
            @include('layouts.partials/page-title', ['title' => $title, 'subTitle' => $subTitle])

            @yield('content')
        </div>

        <footer class="footer">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12 text-center">
                        © {{ date('Y') }} Pulsify · Built by Brick2Bytes Solutions FZE
                    </div>
                </div>
            </div>
        </footer>
    </div>

</div>

@include('layouts.partials/right-sidebar')
@include('layouts.partials/footer-scripts')

@yield('scripts')
</body>
</html>

