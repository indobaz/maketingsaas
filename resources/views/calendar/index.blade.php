@extends('layouts.dashboard')

@php
    $pageTitle = 'Content Calendar';

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

    $pillarColors = $pillars->mapWithKeys(fn ($p) => [$p->name => $p->color ?? '#94a3b8']);
    $pillarBalanceOk = collect($pillarBalance['rows'])->every(fn ($r) => $r['ok']);

    $postTypeLabelsJs = [
        'static_image' => 'Static image',
        'carousel' => 'Carousel',
        'short_video' => 'Short video / Reel',
        'long_video' => 'Long-form video',
        'story' => 'Story',
        'text_post' => 'Text / thread',
        'poll' => 'Poll',
        'ugc' => 'UGC / repost',
    ];

    $statusLabelsJs = [
        'draft' => 'Draft',
        'in_review' => 'In Review',
        'approved' => 'Approved',
        'scheduled' => 'Scheduled',
        'published' => 'Published',
        'rejected' => 'Rejected',
    ];
@endphp

@section('content')
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <span class="text-muted small d-md-none fw-semibold">{{ $pageTitle }}</span>
        <a href="{{ route('content.create') }}" class="btn text-white ms-md-auto"
           style="background-color: var(--pulsify-accent, #5F63F2);">
            Create Post
        </a>
    </div>

    <div class="row g-3 align-items-start">
        <div class="col-lg-8 col-xl-9" id="calendar-main-col">
            <div class="card mb-3">
                <div class="card-body">
                    <div class="row g-2 align-items-end mb-3">
                        <div class="col-md-4">
                            <label for="filter-channel" class="form-label small text-muted mb-1">Channel</label>
                            <select id="filter-channel" class="form-select form-select-sm">
                                <option value="">All channels</option>
                                @foreach($channels as $ch)
                                    <option value="{{ $ch->id }}">{{ $ch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="filter-pillar" class="form-label small text-muted mb-1">Pillar</label>
                            <select id="filter-pillar" class="form-select form-select-sm">
                                <option value="">All pillars</option>
                                @foreach($pillars as $pillar)
                                    <option value="{{ $pillar->name }}">{{ $pillar->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="filter-status" class="form-label small text-muted mb-1">Status</label>
                            <select id="filter-status" class="form-select form-select-sm">
                                <option value="all">All</option>
                                <option value="draft">Draft</option>
                                <option value="in_review">In Review</option>
                                <option value="approved">Approved</option>
                                <option value="scheduled">Scheduled</option>
                                <option value="published">Published</option>
                            </select>
                        </div>
                        <div class="col-md-1 d-grid">
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="filter-apply">Filter</button>
                        </div>
                    </div>

                    <div id="calendar"></div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                        <span class="fw-semibold small">This month&rsquo;s pillar balance</span>
                        @if($pillarBalance['total'] > 0)
                            <span class="badge {{ $pillarBalanceOk ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' }}">
                                {{ $pillarBalanceOk ? 'On target (±5%)' : 'Review mix' }}
                            </span>
                        @endif
                    </div>

                    @if($pillarBalance['total'] === 0)
                        <p class="text-muted small mb-0">No posts with dates in the current month yet.</p>
                    @else
                        <div class="rounded overflow-hidden d-flex mb-2" style="height: 14px; min-height: 14px;"
                             role="img" aria-label="Pillar distribution">
                            @foreach($pillarBalance['rows'] as $row)
                                @if($row['count'] > 0)
                                    <div style="flex: {{ $row['count'] }} 1 0; background-color: {{ $row['color'] }}; min-width: 4px;"
                                         title="{{ $row['name'] }}: {{ $row['actual'] }}% (target {{ $row['target'] }}%)"></div>
                                @endif
                            @endforeach
                            @if($pillarBalance['none_count'] > 0)
                                <div style="flex: {{ $pillarBalance['none_count'] }} 1 0; background-color: #cbd5e1; min-width: 4px;"
                                     title="Unassigned pillar: {{ $pillarBalance['none_count'] }}"></div>
                            @endif
                        </div>
                        <ul class="list-unstyled small mb-0">
                            @foreach($pillarBalance['rows'] as $row)
                                <li class="d-flex align-items-center gap-2 py-1 border-bottom border-light">
                                    <span class="rounded-circle flex-shrink-0" style="width:10px;height:10px;background:{{ $row['color'] }};"></span>
                                    <span class="flex-grow-1">{{ $row['name'] }}</span>
                                    <span class="text-muted">{{ $row['count'] }} posts</span>
                                    <span class="{{ $row['ok'] ? 'text-success' : 'text-warning' }}">
                                        {{ $row['actual'] }}% / {{ $row['target'] }}% target
                                    </span>
                                </li>
                            @endforeach
                            @if($pillarBalance['none_count'] > 0)
                                <li class="d-flex align-items-center gap-2 py-1 text-muted">
                                    <span class="rounded-circle flex-shrink-0" style="width:10px;height:10px;background:#cbd5e1;"></span>
                                    <span>Unassigned</span>
                                    <span>{{ $pillarBalance['none_count'] }} posts</span>
                                </li>
                            @endif
                        </ul>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-xl-3">
            <div class="card">
                <div class="card-header py-2">
                    <button type="button" class="btn btn-link btn-sm text-decoration-none p-0 text-body fw-semibold w-100 text-start"
                            data-bs-toggle="collapse" data-bs-target="#unscheduled-sidebar" aria-expanded="true"
                            aria-controls="unscheduled-sidebar">
                        Unscheduled posts
                        <span class="badge bg-secondary ms-1">{{ $unscheduledCount }} unscheduled</span>
                    </button>
                </div>
                <div class="collapse show" id="unscheduled-sidebar">
                    <div class="card-body pt-0" style="max-height: 70vh; overflow-y: auto;">
                        @forelse($unscheduledPosts as $post)
                            @php
                                $plat = $post->channel?->platform ?? 'custom';
                                $rawTitle = $post->title;
                                $line = ($rawTitle !== null && $rawTitle !== '')
                                    ? $rawTitle
                                    : \Illuminate\Support\Str::limit((string) ($post->caption_en ?? ''), 60);
                            @endphp
                            <div class="d-flex align-items-start gap-2 py-2 border-bottom border-light">
                                <div class="flex-shrink-0" style="line-height:0;">
                                    @include('calendar.partials.platform-icon', ['platform' => $plat, 'suffix' => 'us-'.$post->id, 'size' => 28])
                                </div>
                                <div class="min-w-0 flex-grow-1">
                                    <div class="small fw-medium text-truncate" title="{{ $line }}">{{ $line }}</div>
                                    <span class="badge {{ $statusBadgeClass($post->status) }} badge-sm mt-1" style="font-size: 0.65rem;">
                                        {{ $statusLabel($post->status) }}
                                    </span>
                                </div>
                                <div class="flex-shrink-0 d-flex flex-column gap-1">
                                    <a href="{{ route('content.edit', $post) }}" class="btn btn-sm btn-outline-primary py-0 px-2" style="font-size: 11px;">Schedule</a>
                                    <button type="button" class="btn btn-sm btn-link py-0 px-1 text-muted focus-calendar-btn" style="font-size: 11px;"
                                            data-focus-calendar="1">Calendar</button>
                                </div>
                            </div>
                        @empty
                            <p class="text-muted small mb-0">No unscheduled posts.</p>
                        @endforelse
                        @if($unscheduledCount > 75)
                            <p class="text-muted small mt-2 mb-0">Showing 75 of {{ $unscheduledCount }}. Narrow down in Content Library.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="platform-templates" class="d-none" aria-hidden="true">
        @foreach($platformKeys as $pk)
            <div data-platform="{{ $pk }}">
                @include('calendar.partials.platform-icon', ['platform' => $pk, 'suffix' => 'tpl-'.$pk, 'size' => 32])
            </div>
        @endforeach
    </div>

    <div class="modal fade" id="calendar-event-modal" tabindex="-1" aria-labelledby="calendar-event-modal-title" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="calendar-event-modal-title"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <div id="cal-modal-platform" style="line-height:0;"></div>
                        <span id="cal-modal-channel-name" class="fw-medium"></span>
                    </div>
                    <div class="d-flex flex-wrap gap-2 mb-2">
                        <span id="cal-modal-post-type" class="badge bg-light text-dark border"></span>
                        <span id="cal-modal-status" class="badge"></span>
                    </div>
                    <div class="d-flex align-items-center gap-2 mb-2" id="cal-modal-pillar-row" hidden>
                        <span class="rounded-circle flex-shrink-0" id="cal-modal-pillar-dot" style="width:10px;height:10px;"></span>
                        <span id="cal-modal-pillar-name"></span>
                    </div>
                    <p class="small text-muted mb-1">Dates</p>
                    <p class="small mb-2" id="cal-modal-dates"></p>
                    <p class="small text-muted mb-1">Caption preview</p>
                    <p class="small mb-3" id="cal-modal-caption"></p>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="#" class="btn btn-primary btn-sm" id="cal-modal-edit">Edit Post</a>
                        <a href="#" class="btn btn-outline-secondary btn-sm" id="cal-modal-library">View in Library</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
    <style>
        .fc-event-published {
            opacity: 0.72;
            filter: saturate(0.85);
        }
        .fc .fc-toolbar-title {
            font-size: 1.1rem;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const csrfToken = @json(csrf_token());
            const eventsUrl = @json(route('calendar.events'));
            const contentIndexUrl = @json(url('/content'));
            const pillarColors = @json($pillarColors);
            const postTypeLabels = @json($postTypeLabelsJs);
            const statusLabels = @json($statusLabelsJs);
            const statusBadgeClass = {
                draft: 'bg-secondary',
                in_review: 'bg-warning text-dark',
                approved: 'bg-info text-dark',
                scheduled: 'bg-primary',
                published: 'bg-success',
                rejected: 'bg-danger',
            };

            function buildFilterParams() {
                const p = new URLSearchParams();
                const ch = document.getElementById('filter-channel').value;
                const pillar = document.getElementById('filter-pillar').value;
                const st = document.getElementById('filter-status').value;
                if (ch) {
                    p.set('channel_id', ch);
                }
                if (pillar) {
                    p.set('pillar', pillar);
                }
                if (st && st !== 'all') {
                    p.set('status', st);
                }
                return p;
            }

            const calendarEl = document.getElementById('calendar');
            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,listWeek',
                },
                height: 'auto',
                editable: true,
                events: function (fetchInfo, successCallback, failureCallback) {
                    const p = buildFilterParams();
                    p.set('start', fetchInfo.startStr);
                    p.set('end', fetchInfo.endStr);
                    fetch(eventsUrl + '?' + p.toString(), {
                        headers: { Accept: 'application/json' },
                        credentials: 'same-origin',
                    })
                        .then(function (r) {
                            if (!r.ok) {
                                throw new Error('Events request failed');
                            }
                            return r.json();
                        })
                        .then(successCallback)
                        .catch(function () {
                            failureCallback();
                        });
                },
                eventClick: function (info) {
                    const p = info.event.extendedProps;
                    const modalEl = document.getElementById('calendar-event-modal');
                    const title = p.title_full && String(p.title_full).trim() !== ''
                        ? p.title_full
                        : (info.event.title || 'Untitled');
                    document.getElementById('calendar-event-modal-title').textContent = title;
                    document.getElementById('cal-modal-channel-name').textContent = p.channel_name || '—';

                    const platSlot = document.getElementById('cal-modal-platform');
                    platSlot.innerHTML = '';
                    const platKey = p.platform || 'custom';
                    const tpl = document.querySelector('#platform-templates [data-platform="' + platKey + '"]')
                        || document.querySelector('#platform-templates [data-platform="custom"]');
                    if (tpl && tpl.firstElementChild) {
                        const clone = tpl.firstElementChild.cloneNode(true);
                        clone.querySelectorAll('linearGradient[id],radialGradient[id]').forEach(function (grad) {
                            const oldId = grad.getAttribute('id');
                            if (!oldId) {
                                return;
                            }
                            const newId = oldId + '-modal-' + String(p.post_id);
                            grad.setAttribute('id', newId);
                            clone.querySelectorAll('[fill]').forEach(function (el) {
                                const f = el.getAttribute('fill');
                                if (f === 'url(#' + oldId + ')') {
                                    el.setAttribute('fill', 'url(#' + newId + ')');
                                }
                            });
                        });
                        platSlot.appendChild(clone);
                    }

                    const pt = p.post_type || '';
                    document.getElementById('cal-modal-post-type').textContent = postTypeLabels[pt] || (pt ? pt.replace(/_/g, ' ') : '—');

                    const st = p.status || '';
                    const stBadge = document.getElementById('cal-modal-status');
                    stBadge.textContent = statusLabels[st] || st || '—';
                    stBadge.className = 'badge ' + (statusBadgeClass[st] || 'bg-light text-dark border');

                    const pillarRow = document.getElementById('cal-modal-pillar-row');
                    const pillarName = p.pillar;
                    if (pillarName) {
                        pillarRow.hidden = false;
                        document.getElementById('cal-modal-pillar-name').textContent = pillarName;
                        const dot = document.getElementById('cal-modal-pillar-dot');
                        dot.style.background = pillarColors[pillarName] || '#94a3b8';
                    } else {
                        pillarRow.hidden = true;
                    }

                    let datesText = '';
                    if (p.scheduled_at) {
                        datesText += 'Scheduled: ' + new Date(p.scheduled_at).toLocaleString();
                    }
                    if (p.published_at) {
                        if (datesText) {
                            datesText += '\n';
                        }
                        datesText += 'Published: ' + new Date(p.published_at).toLocaleString();
                    }
                    document.getElementById('cal-modal-dates').textContent = datesText || '—';

                    const cap = p.caption_en ? String(p.caption_en) : '';
                    document.getElementById('cal-modal-caption').textContent = cap.length > 100 ? cap.slice(0, 100) + '…' : (cap || '—');

                    const pid = p.post_id;
                    document.getElementById('cal-modal-edit').href = contentIndexUrl + '/' + encodeURIComponent(pid) + '/edit';

                    const searchQ = title.length > 80 ? title.slice(0, 80) : title;
                    document.getElementById('cal-modal-library').href = contentIndexUrl + '?search=' + encodeURIComponent(searchQ);

                    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                    modal.show();
                },
                eventDrop: function (info) {
                    if (info.event.extendedProps.status === 'published' || info.event.start === null) {
                        info.revert();
                        return;
                    }
                    const postId = info.event.extendedProps.post_id;
                    const iso = info.event.start ? info.event.start.toISOString() : null;
                    if (!postId || !iso) {
                        info.revert();
                        return;
                    }
                    fetch(@json(url('/calendar/reschedule')) + '/' + encodeURIComponent(postId), {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: JSON.stringify({ scheduled_at: iso }),
                    }).then(function (r) {
                        if (!r.ok) {
                            info.revert();
                        }
                    }).catch(function () {
                        info.revert();
                    });
                },
            });

            calendar.render();

            document.getElementById('filter-apply').addEventListener('click', function () {
                calendar.refetchEvents();
            });

            document.querySelectorAll('.focus-calendar-btn').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    calendarEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    calendarEl.classList.add('border', 'border-primary', 'rounded');
                    setTimeout(function () {
                        calendarEl.classList.remove('border', 'border-primary', 'rounded');
                    }, 1600);
                });
            });
        });
    </script>
@endsection
