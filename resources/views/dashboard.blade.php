@extends('layouts.dashboard')

@php
    $pageTitle = 'Dashboard';

    $hour = (int) now()->format('G');
    $dayPart = $hour < 12 ? 'morning' : ($hour < 17 ? 'afternoon' : 'evening');

    $user = auth()->user();
    $company = $user?->company;
    $primaryColor = $company?->primary_color ?? '#5F63F2';

    $statusLabel = static function (?string $status): string {
        return match ($status) {
            'draft' => 'Draft',
            'in_review' => 'In Review',
            'approved' => 'Approved',
            'scheduled' => 'Scheduled',
            'published' => 'Published',
            'rejected' => 'Rejected',
            default => $status ? ucfirst(str_replace('_', ' ', $status)) : '—',
        };
    };

    $statusBadgeClass = static function (?string $status): string {
        return match ($status) {
            'draft' => 'bg-secondary',
            'in_review' => 'bg-warning text-dark',
            'approved' => 'bg-info text-dark',
            'scheduled' => 'bg-primary',
            'published' => 'bg-success',
            'rejected' => 'bg-danger',
            default => 'bg-light text-dark border',
        };
    };

    $activityVerbLine = static function ($comment): string {
        $post = $comment->post;
        $title = \Illuminate\Support\Str::limit((string) ($post?->title ?? 'Untitled'), 40);
        if ($comment->status_change === null) {
            return 'commented on '.$title;
        }

        return match ($comment->status_change) {
            'in_review' => 'submitted '.$title.' for review',
            'approved' => 'approved '.$title,
            'rejected' => 'rejected '.$title,
            'published' => 'published '.$title,
            default => 'updated '.$title,
        };
    };

    $trendClass = $thisWeekPosts > $lastWeekPosts ? 'text-success' : ($thisWeekPosts < $lastWeekPosts ? 'text-danger' : 'text-muted');
@endphp

@section('content')
    <div class="d-flex align-items-start justify-content-between mb-4 flex-wrap gap-2">
        <div>
            <div class="fw-semibold fs-5">
                Good {{ $dayPart }}, {{ $user?->name }}! 👋
            </div>
            <div class="text-muted mt-1">
                {{ now()->format('l, F j, Y') }} · Here's your workspace overview.
            </div>
        </div>
        <div class="d-none d-md-flex align-items-center gap-2">
            <span class="badge bg-soft-primary text-primary">{{ $company?->name ?? 'Pulsify' }}</span>
            @if($user?->role)
                <span class="badge bg-soft-secondary text-secondary">{{ $user->role }}</span>
            @endif
        </div>
    </div>

    @php
        $kpiBox = 'background:#fff;border-radius:12px;border:1px solid #F0F2F5;box-shadow:0 1px 3px rgba(15,23,42,0.06);padding:20px;';
    @endphp

    {{-- ROW 1: KPI cards --}}
    <div class="row g-3">
        <div class="col-12 col-md-6 col-lg-3">
            <div class="h-100" style="{{ $kpiBox }}">
                <div class="text-muted fw-medium small">Total Posts</div>
                <div class="mt-1 fs-2 fw-bold text-dark">{{ number_format($postCount) }}</div>
                <div class="small mt-1 {{ $trendClass }} fw-medium">
                    +{{ number_format($thisWeekPosts) }} this week
                </div>
                <div id="kpi-posts-sparkline" class="mt-2" style="min-height:60px;"></div>
                <a href="{{ route('content.index') }}" class="small text-decoration-none d-inline-block mt-2" style="color: {{ $primaryColor }};">View content →</a>
            </div>
        </div>

        <div class="col-12 col-md-6 col-lg-3">
            <a href="{{ route('channels.index') }}" class="text-decoration-none text-dark d-block h-100">
                <div class="h-100" style="{{ $kpiBox }}">
                    <div class="text-muted fw-medium small">Channels Connected</div>
                    <div class="mt-1 fs-2 fw-bold">{{ number_format($channelCount) }}</div>
                    <div class="small text-muted mt-2">
                        {{ number_format($channelsActive) }} active, {{ number_format($channelsArchived) }} archived
                    </div>
                </div>
            </a>
        </div>

        <div class="col-12 col-md-6 col-lg-3">
            <div class="h-100 position-relative" style="{{ $kpiBox }}{{ $tasksDueToday > 0 ? 'border-left:3px solid #FF4D4F;' : '' }}">
                @if($tasksDueToday > 0)
                    <span class="position-absolute top-0 end-0 mt-3 me-3 rounded-circle" style="width:10px;height:10px;background:#FF4D4F;" title="Tasks need attention"></span>
                @endif
                <div class="text-muted fw-medium small">Tasks Due Today</div>
                <div class="mt-1 fs-2 fw-bold text-dark">{{ number_format($tasksDueToday) }}</div>
                <a href="{{ route('tasks.index') }}" class="small text-decoration-none d-inline-block mt-3 fw-medium" style="color: {{ $primaryColor }};">View tasks →</a>
            </div>
        </div>

        <div class="col-12 col-md-6 col-lg-3">
            <a href="{{ url('/team') }}" class="text-decoration-none text-dark d-block h-100">
                <div class="h-100" style="{{ $kpiBox }}">
                    <div class="text-muted fw-medium small">Team Members</div>
                    <div class="mt-1 fs-2 fw-bold">{{ number_format($teamCount) }}</div>
                    <div class="d-flex align-items-center mt-3">
                        @forelse($teamPreview as $member)
                            @php
                                $label = (string) ($member->name ?? $member->email ?? '?');
                                $initial = strtoupper(mb_substr($label, 0, 1));
                            @endphp
                            <span class="rounded-circle text-white fw-semibold d-inline-flex align-items-center justify-content-center border border-white"
                                  style="width:32px;height:32px;font-size:13px;background:{{ $primaryColor }};margin-left:{{ $loop->first ? 0 : -8 }}px;z-index:{{ 10 - $loop->index }};">
                                {{ $initial }}
                            </span>
                        @empty
                            <span class="text-muted small">No members yet</span>
                        @endforelse
                    </div>
                </div>
            </a>
        </div>
    </div>

    {{-- ROW 2: Charts (60% / 40% on large screens) --}}
    <div class="d-flex flex-column flex-lg-row gap-3 mt-1">
        <div style="flex: 1 1 60%; min-width: 0;">
            <div class="h-100" style="{{ $kpiBox }}">
                <div class="fw-semibold mb-2">Content Overview</div>
                @if($postCount > 0)
                    <div id="chart-status-donut"></div>
                    <div id="chart-status-legend" class="small text-muted mt-2"></div>
                @else
                    <p class="text-muted mb-0 small">Create your first post to see status distribution.</p>
                @endif
            </div>
        </div>
        <div style="flex: 1 1 40%; min-width: 0;">
            <div class="h-100" style="{{ $kpiBox }}">
                <div class="fw-semibold mb-2">Content by Pillar</div>
                @if(count($postsByPillar) > 0)
                    <div id="chart-pillar-bar"></div>
                @else
                    <p class="text-muted mb-0 small">Set up content pillars to see this chart.</p>
                @endif
            </div>
        </div>
    </div>

    {{-- ROW 3: Upcoming + Activity (45% / 55% on large screens) --}}
    <div class="d-flex flex-column flex-lg-row gap-3 mt-1">
        <div style="flex: 1 1 45%; min-width: 0;">
            <div class="h-100" style="{{ $kpiBox }}">
                <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
                    <div class="fw-semibold">Scheduled Posts</div>
                    <span class="badge rounded-pill bg-soft-primary text-primary">{{ number_format($upcomingPostsCount) }}</span>
                </div>
                @forelse($upcomingPosts as $post)
                    <div class="d-flex gap-2 align-items-start {{ !$loop->last ? 'pb-3 mb-3 border-bottom' : '' }}" style="border-color:#F0F2F5 !important;">
                        <div class="flex-shrink-0 d-flex align-items-center justify-content-center" style="width:28px;height:28px;">
                            @include('calendar.partials.platform-icon', ['platform' => $post->channel?->platform ?? 'custom', 'size' => 28, 'suffix' => 'dash-'.$post->id])
                        </div>
                        <div class="min-w-0 flex-grow-1">
                            <div class="fw-medium text-truncate" title="{{ $post->title }}">{{ \Illuminate\Support\Str::limit((string) $post->title, 40) }}</div>
                            <div class="small text-muted">{{ $post->channel?->name ?? '—' }}</div>
                            @php $dt = $post->scheduled_at; @endphp
                            <div class="small mt-1">
                                @if($dt->isToday())
                                    Today {{ $dt->format('g:i A') }}
                                @elseif($dt->isTomorrow())
                                    Tomorrow {{ $dt->format('g:i A') }}
                                @else
                                    {{ $dt->format('M j, Y') }}
                                @endif
                            </div>
                            <span class="badge {{ $statusBadgeClass($post->status) }} badge-sm mt-1" style="font-size:0.65rem;">{{ $statusLabel($post->status) }}</span>
                        </div>
                    </div>
                @empty
                    <p class="text-muted small mb-0">No posts scheduled. Schedule a post on the Calendar.</p>
                @endforelse
                <div class="mt-3 pt-2 border-top" style="border-color:#F0F2F5 !important;">
                    <a href="{{ route('calendar.index') }}" class="small text-decoration-none fw-medium" style="color: {{ $primaryColor }};">View Calendar →</a>
                </div>
            </div>
        </div>
        <div style="flex: 1 1 55%; min-width: 0;">
            <div class="h-100" style="{{ $kpiBox }}">
                <div class="fw-semibold mb-3">Recent Activity</div>
                @forelse($recentActivity as $comment)
                    <div class="d-flex gap-2 align-items-start {{ !$loop->last ? 'pb-3 mb-3' : '' }}">
                        <div class="flex-shrink-0">
                            @php
                                $actor = $comment->user;
                                $actorLabel = (string) ($actor?->name ?? $actor?->email ?? '?');
                                $actorInitial = strtoupper(mb_substr($actorLabel, 0, 1));
                            @endphp
                            <span class="rounded-circle text-white fw-semibold d-inline-flex align-items-center justify-content-center"
                                  style="width:24px;height:24px;font-size:11px;background:{{ $primaryColor }};">
                                {{ $actorInitial }}
                            </span>
                        </div>
                        <div class="min-w-0 flex-grow-1">
                            <div class="small">
                                <span class="fw-semibold">{{ $actorLabel }}</span>
                                <span class="text-muted">{{ $activityVerbLine($comment) }}</span>
                            </div>
                            <div class="small text-muted">{{ $comment->created_at->diffForHumans() }}</div>
                            @if($comment->status_change)
                                <span class="badge {{ $statusBadgeClass($comment->status_change) }} badge-sm mt-1" style="font-size:0.65rem;">{{ $statusLabel($comment->status_change) }}</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-muted small mb-0">No recent activity yet.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- ROW 4: AI Briefing --}}
    <div class="row g-3 mt-1">
        <div class="col-12">
            <div class="rounded-3 p-4 d-flex flex-wrap align-items-center gap-3"
                 style="background: linear-gradient(135deg, rgba(95,99,242,0.12) 0%, rgba(168,85,247,0.14) 100%); border: 1px solid rgba(95,99,242,0.2);">
                <div class="avatar-md flex-shrink-0">
                    <span class="avatar-title rounded-circle d-inline-flex align-items-center justify-content-center text-white"
                          style="width:48px;height:48px;background: {{ $primaryColor }};">
                        <i class="bi bi-robot fs-4"></i>
                    </span>
                </div>
                <div class="flex-grow-1 min-w-0">
                    <div class="fw-semibold">AI Daily Briefing — Coming in Phase 3</div>
                    <div class="text-muted mt-1 mb-0 small">Your AI marketing briefing will appear here daily.</div>
                </div>
                <span class="badge align-self-start" style="background: rgba(95,99,242,0.2); color: #4338CA; font-weight: 700;">Phase 3</span>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.49.1/dist/apexcharts.min.js"></script>
    <script>
        (function () {
            if (typeof ApexCharts === 'undefined') {
                console.error('Pulsify dashboard: ApexCharts failed to load (CDN). Charts will not render.');
                return;
            }

            var sparkData = @json($postsSparklineLast7Days ?? []);
            if (document.getElementById('kpi-posts-sparkline')) {
                new ApexCharts(document.querySelector('#kpi-posts-sparkline'), {
                    chart: { type: 'area', sparkline: { enabled: true }, height: 60, toolbar: { show: false } },
                    stroke: { curve: 'smooth', width: 2 },
                    fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.35, opacityTo: 0.05 } },
                    series: [{ name: 'Posts', data: sparkData }],
                    colors: ['{{ $primaryColor }}'],
                    tooltip: { enabled: false },
                    dataLabels: { enabled: false },
                    xaxis: { labels: { show: false } },
                    yaxis: { labels: { show: false } },
                    grid: { show: false }
                }).render();
            }

            var statusOrder = [
                { key: 'draft', label: 'Draft', color: '#9299B8' },
                { key: 'in_review', label: 'In review', color: '#FA8B0C' },
                { key: 'approved', label: 'Approved', color: '#20C997' },
                { key: 'scheduled', label: 'Scheduled', color: '#8B5CF6' },
                { key: 'published', label: 'Published', color: '#5F63F2' },
                { key: 'rejected', label: 'Rejected', color: '#FF4D4F' }
            ];
            var statusRaw = @json($postsByStatus ?? []);
            var statusSeries = [];
            var statusColors = [];
            var statusLabels = [];
            statusOrder.forEach(function (row) {
                var n = parseInt(statusRaw[row.key] || 0, 10);
                if (n > 0) {
                    statusSeries.push(n);
                    statusColors.push(row.color);
                    statusLabels.push(row.label);
                }
            });
            var totalPosts = {{ (int) $postCount }};
            if (statusSeries.length && document.getElementById('chart-status-donut')) {
                var donut = new ApexCharts(document.querySelector('#chart-status-donut'), {
                    chart: { type: 'donut', height: 320, toolbar: { show: false } },
                    labels: statusLabels,
                    series: statusSeries,
                    colors: statusColors,
                    legend: { show: false },
                    plotOptions: {
                        pie: {
                            donut: {
                                size: '72%',
                                labels: {
                                    show: true,
                                    name: { show: false },
                                    value: { show: false },
                                    total: {
                                        show: true,
                                        showAlways: true,
                                        label: 'Total posts',
                                        fontSize: '12px',
                                        formatter: function () { return totalPosts; }
                                    }
                                }
                            }
                        }
                    },
                    dataLabels: { enabled: false },
                    tooltip: { y: { formatter: function (val) { return val; } } }
                });
                donut.render().then(function () {
                    var el = document.getElementById('chart-status-legend');
                    if (!el) return;
                    var parts = [];
                    statusOrder.forEach(function (row) {
                        var n = parseInt(statusRaw[row.key] || 0, 10);
                        parts.push('<span class="d-inline-flex align-items-center me-3 mb-1"><span class="rounded-circle me-1" style="width:8px;height:8px;background:' + row.color + '"></span>' + row.label + ' <span class="ms-1 fw-semibold text-dark">' + n + '</span></span>');
                    });
                    el.innerHTML = parts.join('');
                });
            }

            var pillars = @json($postsByPillar ?? []);
            if (pillars.length && document.getElementById('chart-pillar-bar')) {
                var names = pillars.map(function (p) { return p.name; });
                var counts = pillars.map(function (p) { return p.count; });
                var pColors = pillars.map(function (p) { return p.color; });
                new ApexCharts(document.querySelector('#chart-pillar-bar'), {
                    chart: { type: 'bar', height: Math.max(220, pillars.length * 36), toolbar: { show: false } },
                    series: [{ name: 'Posts', data: counts }],
                    plotOptions: {
                        bar: {
                            horizontal: true,
                            distributed: true,
                            barHeight: '70%',
                            borderRadius: 4
                        }
                    },
                    colors: pColors,
                    dataLabels: {
                        enabled: true,
                        formatter: function (val) { return val; },
                        style: { fontSize: '12px', colors: ['#272B41'] }
                    },
                    xaxis: { categories: names, labels: { style: { fontSize: '11px' } } },
                    yaxis: { labels: { show: false } },
                    legend: { show: false },
                    grid: { borderColor: '#F0F2F5', strokeDashArray: 4 }
                }).render();
            }
        })();
    </script>
@endsection
