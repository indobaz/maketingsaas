@extends('layouts.dashboard')

@php
    $pageTitle = 'Dashboard';

    $hour = (int) now()->format('G');
    $dayPart = $hour < 12 ? 'morning' : ($hour < 17 ? 'afternoon' : 'evening');

    $user = auth()->user();
    $company = $user?->company;
    $primaryColor = $company?->primary_color ?? 'var(--color-primary)';
@endphp

@section('content')
    <div class="d-flex align-items-start justify-content-between mb-4">
        <div>
            <div class="fw-semibold fs-5">
                Good {{ $dayPart }}, {{ $user?->name }}!
            </div>
            <div class="text-muted mt-1">
                Here’s a quick snapshot of your workspace today.
            </div>
        </div>
        <div class="d-none d-md-flex align-items-center gap-2">
            <span class="badge bg-soft-primary text-primary">{{ $company?->name ?? 'Pulsify' }}</span>
            @if($user?->role)
                <span class="badge bg-soft-secondary text-secondary">{{ $user->role }}</span>
            @endif
        </div>
    </div>

    <div class="row g-3">
        @php
            $kpis = [
                [
                    'label' => 'Total Posts',
                    'value' => $totalPosts ?? 0,
                    'icon' => 'bi-file-text',
                    'bg' => $primaryColor,
                    'link' => url('/content'),
                ],
                [
                    'label' => 'Channels Connected',
                    'value' => $channelsConnected ?? 0,
                    'icon' => 'bi-broadcast',
                    'bg' => 'var(--color-success)',
                    'link' => url('/channels'),
                ],
                [
                    'label' => 'Tasks Due Today',
                    'value' => $tasksDueToday ?? 0,
                    'icon' => 'bi-check2-square',
                    'bg' => 'var(--color-warning)',
                    'link' => url('/tasks'),
                ],
                [
                    'label' => 'Team Members',
                    'value' => $teamMembers ?? 0,
                    'icon' => 'bi-people',
                    'bg' => '#6366F1',
                    'link' => url('/team'),
                ],
            ];
        @endphp

        @foreach($kpis as $kpi)
            <div class="col-12 col-md-6 col-lg-3">
                <div class="card h-100">
                    <div class="card-body position-relative">
                        <a href="{{ $kpi['link'] }}" class="stretched-link" aria-label="Open {{ $kpi['label'] }}"></a>

                        <div class="d-flex align-items-start justify-content-between">
                            <div>
                                <div class="text-muted fw-medium">{{ $kpi['label'] }}</div>
                                <div class="mt-2 fs-2 fw-bold text-dark">
                                    {{ number_format((int) $kpi['value']) }}
                                </div>
                            </div>
                            <div class="avatar-md">
                                <span class="avatar-title rounded-circle text-white" style="background: {{ $kpi['bg'] }};">
                                    <i class="bi {{ $kpi['icon'] }} fs-4"></i>
                                </span>
                            </div>
                        </div>

                        <i class="bi {{ $kpi['icon'] }} widget-icon" style="color: {{ $kpi['bg'] }};"></i>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row g-3 mt-2">
        <div class="col-12 col-lg-6">
            <div class="card border-0" style="background: rgba(99, 102, 241, 0.10);">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <div class="avatar-sm">
                                <span class="avatar-title rounded-circle" style="background: rgba(99, 102, 241, 0.20); color: #4F46E5;">
                                    <i class="bi bi-robot"></i>
                                </span>
                            </div>
                            <div class="fw-semibold">AI Daily Briefing</div>
                        </div>
                        <span class="badge" style="background: rgba(99, 102, 241, 0.18); color: #4F46E5; font-weight: 700;">Phase 3</span>
                    </div>
                    <div class="text-muted mt-2">
                        Coming in Phase 3
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="fw-semibold">Recent Activity</div>
                        <span class="badge text-bg-light" style="font-weight: 700;">Phase 1</span>
                    </div>
                    <div class="text-muted mt-2">
                        Coming in Phase 1
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

