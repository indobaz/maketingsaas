@extends('layouts.dashboard')

@php
    $pageTitle = 'Edit Post';

    $panelStatusBadge = match ($post->status) {
        'published' => ['class' => '', 'style' => 'background-color:#6f42c1;color:#fff;'],
        'scheduled' => ['class' => 'bg-info text-dark', 'style' => ''],
        'in_review' => ['class' => 'bg-warning text-dark', 'style' => ''],
        'approved' => ['class' => 'bg-success', 'style' => ''],
        'rejected' => ['class' => 'bg-danger', 'style' => ''],
        default => ['class' => 'bg-secondary', 'style' => ''],
    };
    $statusLabel = ucwords(str_replace('_', ' ', $post->status));

    $canApproveReject = $post->status === 'in_review' && $isAdminOrOwner;
    $canPublish = $post->status === 'approved' && $isAdminOrOwner;
@endphp

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger" role="alert">
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if($post->status === 'published')
        <div class="alert mb-3 border-0" style="background-color:#6f42c1;color:#fff;">
            ✓ Published on {{ $post->published_at?->format('M j, Y g:i A') ?? '—' }}
        </div>
        <div class="card mb-3">
            <div class="card-body">
                <p class="mb-0 text-muted">This post is published and cannot be edited or deleted from this screen.</p>
            </div>
        </div>
    @else
        <form method="post" action="{{ route('content.update', $post) }}" id="post-form">
            @csrf
            @method('PUT')
            @include('posts.form-fields', ['post' => $post])
        </form>

        @if($canModify)
            <div class="mt-3">
                <form method="post" action="{{ route('content.destroy', $post) }}"
                      onsubmit="return confirm('Delete this post?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger btn-sm">Delete post</button>
                </form>
            </div>
        @endif
    @endif

    <div class="card mt-4">
        <div class="card-body">
            <h5 class="card-title mb-3">Approval &amp; activity</h5>

            <div class="mb-4">
                <span class="text-muted small d-block mb-1">Current status</span>
                @if($panelStatusBadge['style'] !== '')
                    <span class="badge fs-6" style="{{ $panelStatusBadge['style'] }}">{{ $statusLabel }}</span>
                @else
                    <span class="badge fs-6 {{ $panelStatusBadge['class'] }}">{{ $statusLabel }}</span>
                @endif
            </div>

            @if($post->status === 'approved')
                <div class="alert alert-success py-2 mb-4">
                    ✓ Approved — ready to publish.
                </div>
            @endif

            <h6 class="text-muted small text-uppercase mb-3">Timeline</h6>
            <div class="approval-timeline">
                @forelse($post->comments as $comment)
                    <div class="d-flex gap-3 pb-3 mb-3 border-bottom border-light">
                        <div class="flex-shrink-0 rounded-circle d-flex align-items-center justify-content-center text-white fw-semibold"
                             style="width:40px;height:40px;min-width:40px;background:var(--brand-primary,#5F63F2);font-size:15px;">
                            {{ strtoupper(mb_substr((string) ($comment->user?->name ?? '?'), 0, 1)) }}
                        </div>
                        <div class="min-w-0 flex-grow-1">
                            <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                <span class="fw-semibold small">{{ $comment->user?->name ?? 'Unknown user' }}</span>
                                @if($comment->status_change)
                                    @php
                                        $scLabel = ucwords(str_replace('_', ' ', $comment->status_change));
                                    @endphp
                                    <span class="badge bg-secondary small">{{ $scLabel }}</span>
                                @endif
                                <span class="text-muted small ms-auto">{{ $comment->created_at?->format('M j, Y g:i A') }}</span>
                            </div>
                            <div class="small" style="white-space: pre-wrap;">{{ $comment->comment }}</div>
                        </div>
                    </div>
                @empty
                    <p class="text-muted small mb-0">No activity yet.</p>
                @endforelse
            </div>

            <hr class="my-4">

            <h6 class="text-muted small text-uppercase mb-3">Actions</h6>

            @if(in_array($post->status, ['draft', 'rejected']))
                <form method="post" action="{{ route('posts.submit-review', $post) }}" class="mb-3">
                    @csrf
                    <button type="submit" class="btn text-white" style="background-color: var(--brand-primary, #5F63F2);">
                        @if($post->status === 'rejected')
                            Resubmit for Review
                        @else
                            Submit for Review
                        @endif
                    </button>
                </form>
            @endif

            @if($canApproveReject)
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <form method="post" action="{{ route('posts.approve', $post) }}" class="border rounded p-3 h-100">
                            @csrf
                            <div class="fw-semibold small mb-2 text-success">Approve</div>
                            <div class="mb-2">
                                <label class="form-label small mb-0" for="approve_comment">Comment (optional)</label>
                                <textarea name="comment" id="approve_comment" class="form-control form-control-sm" rows="2" maxlength="500" placeholder="Optional note for the creator"></textarea>
                            </div>
                            <button type="submit" class="btn btn-success btn-sm">Approve</button>
                        </form>
                    </div>
                    <div class="col-md-6">
                        <form method="post" action="{{ route('posts.reject', $post) }}" class="border rounded p-3 h-100 border-warning">
                            @csrf
                            <div class="fw-semibold small mb-2 text-warning">Request revision</div>
                            <div class="mb-2">
                                <label class="form-label small mb-0" for="reject_comment">Reason <span class="text-danger">*</span></label>
                                <textarea name="comment" id="reject_comment" class="form-control form-control-sm" rows="2" maxlength="500" required placeholder="Explain what needs to change"></textarea>
                            </div>
                            <button type="submit" class="btn btn-warning btn-sm text-dark">Request Revision</button>
                        </form>
                    </div>
                </div>
            @endif

            @if($canPublish)
                <form method="post" action="{{ route('posts.publish', $post) }}" class="mb-3">
                    @csrf
                    <button type="submit" class="btn text-white" style="background-color:#6f42c1;">
                        Mark as Published
                    </button>
                </form>
            @endif

            <hr class="my-4">

            <h6 class="text-muted small text-uppercase mb-3">Add comment</h6>
            <form method="post" action="{{ route('posts.comment', $post) }}">
                @csrf
                <div class="mb-2">
                    <label class="form-label small mb-0" for="timeline_comment">Comment</label>
                    <textarea name="comment" id="timeline_comment" class="form-control" rows="3" maxlength="1000" required placeholder="Add a note to the timeline"></textarea>
                </div>
                <button type="submit" class="btn btn-outline-secondary btn-sm">Add Comment</button>
            </form>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-body">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                <h5 class="card-title mb-0">Tasks <span class="badge bg-secondary">{{ $linkedTasks->count() }}</span></h5>
                <button type="button" class="btn btn-sm text-white" data-bs-toggle="modal" data-bs-target="#createTaskModal"
                        style="background-color: var(--pulsify-accent, #5F63F2);">
                    Add Task
                </button>
            </div>

            @php
                $taskPriDot = ['high' => '#dc3545', 'medium' => '#fd7e14', 'low' => '#adb5bd'];
                $taskStatusRowClass = [
                    'todo' => 'bg-secondary-subtle text-secondary',
                    'in_progress' => 'bg-primary-subtle text-primary',
                    'in_review' => 'bg-warning-subtle text-warning',
                    'done' => 'bg-success-subtle text-success',
                ];
                $todayRow = now()->startOfDay();
            @endphp

            @forelse($linkedTasks as $lt)
                @php
                    $dot = $taskPriDot[$lt->priority] ?? '#adb5bd';
                    $sb = $taskStatusRowClass[$lt->status] ?? 'bg-light text-dark';
                    $sl = ucwords(str_replace('_', ' ', $lt->status));
                    $ld = $lt->due_date ? $lt->due_date->copy()->startOfDay() : null;
                    $over = $ld && $ld->lt($todayRow);
                @endphp
                <div class="d-flex flex-wrap align-items-center gap-2 py-2 border-bottom border-light">
                    <span class="rounded-circle flex-shrink-0" style="width:10px;height:10px;background:{{ $dot }};" title="{{ $lt->priority }}"></span>
                    <a href="{{ route('tasks.index') }}" class="fw-medium text-body text-decoration-none flex-grow-1 min-w-0">{{ $lt->title }}</a>
                    @if($lt->assignee)
                        <span class="rounded-circle bg-pulsify-accent text-white d-inline-flex align-items-center justify-content-center flex-shrink-0"
                              style="width:26px;height:26px;font-size:11px;">
                            {{ strtoupper(mb_substr($lt->assignee->name ?: $lt->assignee->email, 0, 1)) }}
                        </span>
                    @endif
                    <span class="badge {{ $sb }} small">{{ $sl }}</span>
                    @if($lt->due_date)
                        <span class="small @if($over) text-danger fw-semibold @else text-muted @endif">
                            {{ $lt->due_date->format('M j, Y') }}
                        </span>
                    @else
                        <span class="small text-muted">—</span>
                    @endif
                </div>
            @empty
                <p class="text-muted small mb-0">No tasks linked to this post. Create a task to track production steps.</p>
            @endforelse
        </div>
    </div>

    @include('tasks.partials.create-modal', [
        'teamMembers' => $teamMembersForTasks,
        'postsForTaskLink' => $postsForTaskLink,
        'campaignsForTaskLink' => $campaignsForTaskLink,
        'prefillPostId' => $prefillTaskPostId,
    ])
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var modal = document.getElementById('createTaskModal');
            if (!modal) {
                return;
            }
            modal.addEventListener('show.bs.modal', function () {
                var sel = modal.querySelector('select[name="post_id"]');
                if (sel) {
                    sel.value = '{{ $prefillTaskPostId }}';
                }
            });
        });
    </script>
@endsection
