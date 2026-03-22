@extends('layouts.dashboard')

@php
    $pageTitle = 'Content Library';

    $postTypeLabel = static function (?string $type): string {
        if ($type === null || $type === '') {
            return '—';
        }

        return match ($type) {
            'static_image' => 'Static image',
            'carousel' => 'Carousel',
            'short_video' => 'Short video / Reel',
            'long_video' => 'Long-form video',
            'story' => 'Story',
            'text_post' => 'Text / thread',
            'poll' => 'Poll',
            'ugc' => 'UGC / repost',
            'static' => 'Static image',
            'reel' => 'Short video / Reel',
            'shorts' => 'Short video',
            default => ucfirst(str_replace('_', ' ', $type)),
        };
    };

    $postTypesForFilter = [
        'static_image' => 'Static image',
        'carousel' => 'Carousel',
        'short_video' => 'Short video / Reel',
        'long_video' => 'Long-form video',
        'story' => 'Story',
        'text_post' => 'Text / thread',
        'poll' => 'Poll',
        'ugc' => 'UGC / repost',
        'static' => 'Static image',
        'reel' => 'Short video / Reel',
        'shorts' => 'Short video',
    ];

    $tabs = [
        'all' => 'All',
        'draft' => 'Draft',
        'in_review' => 'In Review',
        'approved' => 'Approved',
        'scheduled' => 'Scheduled',
        'published' => 'Published',
        'rejected' => 'Rejected',
    ];
@endphp

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Toolbar: stats + create (page title comes from layout page-title partial via $pageTitle) --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
        <div class="d-flex flex-wrap align-items-center gap-2">
            <span class="badge rounded-pill bg-light text-secondary border">
                Total Posts <span class="fw-semibold ms-1">{{ $stats['total'] }}</span>
            </span>
            <span class="badge rounded-pill border" style="background:#f3e8ff;color:#6f42c1;border-color:#e9d5ff!important;">
                Published <span class="fw-semibold ms-1">{{ $stats['published'] }}</span>
            </span>
            <span class="badge rounded-pill border" style="background:#fff7ed;color:#c2410c;border-color:#fed7aa!important;">
                In Review <span class="fw-semibold ms-1">{{ $stats['in_review'] }}</span>
            </span>
            <span class="badge rounded-pill bg-light text-dark border">
                Drafts <span class="fw-semibold ms-1">{{ $stats['drafts'] }}</span>
            </span>
        </div>
        <a href="{{ url('/content/create') }}" class="btn text-white" style="background-color: var(--brand-primary, #5F63F2);">
            Create New Post
        </a>
    </div>

    {{-- Filter bar --}}
    <div class="card mb-3">
        <div class="card-body py-3">
            <form method="get" action="{{ route('content.index') }}" id="content-filter-form" class="d-flex flex-wrap align-items-end gap-2 gap-lg-3">
                <input type="hidden" name="status" value="{{ $filter }}">

                <div class="flex-grow-1" style="min-width: 200px;">
                    <label class="form-label small mb-1 text-muted" for="filter-search">Search</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0"><i class="ri-search-line"></i></span>
                        <input type="text" name="search" id="filter-search" value="{{ $filters['search'] }}"
                               class="form-control border-start-0" placeholder="Search posts..." autocomplete="off">
                    </div>
                </div>

                <div style="min-width: 150px;">
                    <label class="form-label small mb-1 text-muted" for="filter-channel">Channel</label>
                    <select name="channel_id" id="filter-channel" class="form-select form-select-sm">
                        <option value="">All Channels</option>
                        @foreach($channels as $ch)
                            <option value="{{ $ch->id }}" @selected((string) $filters['channel_id'] === (string) $ch->id)>
                                {{ $ch->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div style="min-width: 150px;">
                    <label class="form-label small mb-1 text-muted" for="filter-pillar">Pillar</label>
                    <select name="pillar" id="filter-pillar" class="form-select form-select-sm">
                        <option value="">All Pillars</option>
                        @foreach($pillars as $p)
                            <option value="{{ $p->name }}" @selected($filters['pillar'] === $p->name)>{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div style="min-width: 160px;">
                    <label class="form-label small mb-1 text-muted" for="filter-post-type">Post type</label>
                    <select name="post_type" id="filter-post-type" class="form-select form-select-sm">
                        <option value="">All Types</option>
                        @foreach($postTypesForFilter as $val => $plabel)
                            <option value="{{ $val }}" @selected($filters['post_type'] === $val)>{{ $plabel }}</option>
                        @endforeach
                    </select>
                </div>

                <div style="min-width: 130px;">
                    <label class="form-label small mb-1 text-muted" for="filter-date-from">From</label>
                    <input type="date" name="date_from" id="filter-date-from" value="{{ $filters['date_from'] }}" class="form-control form-control-sm">
                </div>

                <div style="min-width: 130px;">
                    <label class="form-label small mb-1 text-muted" for="filter-date-to">To</label>
                    <input type="date" name="date_to" id="filter-date-to" value="{{ $filters['date_to'] }}" class="form-control form-control-sm">
                </div>

                <div class="d-flex align-items-center gap-2 pb-1">
                    <button type="submit" class="btn btn-sm text-white" style="background-color: var(--brand-primary, #5F63F2);">Filter</button>
                    <a href="{{ route('content.index') }}" class="small text-decoration-none">Clear</a>
                </div>
            </form>

            @if($hasActiveFilters)
                <p class="small text-muted mb-0 mt-3"><span class="fw-semibold">{{ $posts->total() }}</span> results found</p>
            @endif
        </div>
    </div>

    {{-- Status tabs --}}
    <ul class="nav nav-pills flex-wrap gap-2 mb-3">
        @foreach($tabs as $key => $label)
            @php
                $tabParams = $retainedFilters;
                if ($key !== 'all') {
                    $tabParams['status'] = $key;
                }
                $isActive = $filter === $key;
                $count = $statusCounts[$key] ?? 0;
                $isInReviewTab = $key === 'in_review';
            @endphp
            <li class="nav-item">
                <a href="{{ route('content.index', $tabParams) }}"
                   class="nav-link py-1 px-3 d-inline-flex align-items-center gap-2 @if($isActive) active @endif"
                   @if($isInReviewTab)
                       style="@if($isActive) background-color:#fff3e0 !important; color:#c2410c !important; border:2px solid #f97316 !important; @else color:#ea580c !important; border:1px solid #fdba74; @endif"
                   @endif>
                    <span>{{ $label }}</span>
                    <span class="badge @if($isActive && $isInReviewTab) bg-dark @elseif($isActive) bg-light text-dark @else bg-secondary @endif">{{ $count }}</span>
                </a>
            </li>
        @endforeach
    </ul>

    {{-- Table --}}
    <div class="card">
        <div class="card-body">
            @if($posts->isEmpty())
                @if($filter === 'all' && ! $hasActiveFilters)
                    <p class="text-muted mb-0">No posts yet. Create your first post.</p>
                @else
                    <p class="text-muted mb-0">No posts match your filters.</p>
                @endif
            @else
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 40px;">
                                    <input type="checkbox" class="form-check-input" disabled title="Bulk actions coming soon" aria-label="Select all">
                                </th>
                                <th>Post</th>
                                <th>Channel</th>
                                <th>Pillar</th>
                                <th>Creator</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($posts as $post)
                                @php
                                    $st = $post->status;
                                    $statusLabel = ucwords(str_replace('_', ' ', $st));
                                    $badgeClass = match ($st) {
                                        'draft' => 'bg-secondary',
                                        'in_review' => 'bg-warning text-dark',
                                        'approved' => 'bg-success',
                                        'scheduled' => 'bg-info text-dark',
                                        'published' => '',
                                        'rejected' => 'bg-danger',
                                        default => 'bg-secondary',
                                    };
                                    $plat = $post->channel?->platform ?? 'custom';
                                    $pillarMeta = $pillars->firstWhere('name', $post->content_pillar);
                                    $pillarColor = $pillarMeta?->color ?? '#e5e7eb';
                                @endphp
                                <tr>
                                    <td>
                                        <input type="checkbox" class="form-check-input" disabled aria-label="Select post" title="Coming soon">
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="flex-shrink-0 d-flex align-items-center justify-content-center overflow-hidden rounded-2 bg-light border" style="width:28px;height:28px;line-height:0;">
                                                @include('posts.partials.platform-logo-svg', ['platform' => $plat, 'uid' => 'idx-plat-'.$post->id, 'size' => 28])
                                            </div>
                                            <div class="min-w-0">
                                                <a href="{{ route('content.edit', $post) }}" class="fw-medium text-body text-decoration-none text-truncate d-block">
                                                    {{ $post->title !== null && $post->title !== '' ? $post->title : 'Untitled' }}
                                                </a>
                                                <span class="badge bg-light text-dark border small fw-normal">{{ $postTypeLabel($post->post_type) }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $post->channel?->name ?? '—' }}</td>
                                    <td>
                                        @if($post->content_pillar)
                                            <span class="d-inline-flex align-items-center gap-2">
                                                <span class="rounded-circle border flex-shrink-0" style="width:10px;height:10px;background:{{ $pillarColor }};"></span>
                                                <span>{{ $post->content_pillar }}</span>
                                            </span>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="small text-muted">{{ $post->creator?->name ?? '—' }}</td>
                                    <td>
                                        @if($st === 'published')
                                            <span class="badge" style="background-color:#6f42c1;color:#fff;">{{ $statusLabel }}</span>
                                        @else
                                            <span class="badge {{ $badgeClass }}">{{ $statusLabel }}</span>
                                        @endif
                                    </td>
                                    <td class="text-nowrap small">{{ $post->created_at?->format('M j, Y g:i A') ?? '—' }}</td>
                                    <td class="text-end text-nowrap">
                                        <a href="{{ route('content.edit', $post) }}" class="btn btn-sm btn-outline-secondary me-1">View</a>
                                        <a href="{{ route('content.edit', $post) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-3 d-flex justify-content-center justify-content-md-end">
                    {{ $posts->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
