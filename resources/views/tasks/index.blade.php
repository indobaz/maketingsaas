@extends('layouts.dashboard')

@php
    use App\Http\Controllers\TaskController;

    $pageTitle = 'Tasks';

    $typeLabels = [
        'content' => 'Content Creation',
        'shooting' => 'Shooting',
        'editing' => 'Editing',
        'design' => 'Design',
        'publishing' => 'Publishing',
        'campaign' => 'Campaign',
        'general' => 'General',
    ];

    $typeBadgeClass = [
        'content' => 'bg-primary-subtle text-primary',
        'shooting' => 'bg-info-subtle text-info',
        'editing' => 'bg-warning-subtle text-warning',
        'design' => 'bg-purple-subtle text-purple',
        'publishing' => 'bg-success-subtle text-success',
        'campaign' => 'bg-danger-subtle text-danger',
        'general' => 'bg-secondary-subtle text-secondary',
    ];

    $statusLabels = [
        'todo' => 'To Do',
        'in_progress' => 'In Progress',
        'in_review' => 'In Review',
        'done' => 'Done',
    ];

    $statusBadgeClass = [
        'todo' => 'bg-secondary-subtle text-secondary',
        'in_progress' => 'bg-primary-subtle text-primary',
        'in_review' => 'bg-warning-subtle text-warning',
        'done' => 'bg-success-subtle text-success',
    ];

    $priorityLabels = ['high' => 'High', 'medium' => 'Medium', 'low' => 'Low'];
    $priorityBadgeClass = [
        'high' => 'bg-danger-subtle text-danger',
        'medium' => 'bg-warning-subtle text-warning',
        'low' => 'bg-secondary-subtle text-secondary',
    ];

    $priorityBorder = ['high' => '#dc3545', 'medium' => '#fd7e14', 'low' => '#adb5bd'];

    $kanbanColumns = [
        'todo' => 'To Do',
        'in_progress' => 'In Progress',
        'in_review' => 'In Review',
        'done' => 'Done',
    ];

    $todayStart = now()->startOfDay();
@endphp

@section('content')
    <style>
        .task-card:hover .task-card-hover-actions { opacity: 1 !important; }
    </style>
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <h4 class="mb-0 fw-semibold d-none d-md-block">{{ $pageTitle }}</h4>
        <div class="d-flex flex-wrap align-items-center gap-2 ms-md-auto">
            <div class="btn-group" role="group" aria-label="View mode">
                <a href="{{ request()->fullUrlWithQuery(['view' => 'kanban']) }}"
                   class="btn btn-sm {{ ($filters['view'] ?? 'kanban') === 'kanban' ? 'btn-dark' : 'btn-outline-secondary' }}">
                    Kanban
                </a>
                <a href="{{ request()->fullUrlWithQuery(['view' => 'list']) }}"
                   class="btn btn-sm {{ ($filters['view'] ?? 'kanban') === 'list' ? 'btn-dark' : 'btn-outline-secondary' }}">
                    List
                </a>
            </div>
            <button type="button" class="btn btn-sm text-white" data-bs-toggle="modal" data-bs-target="#createTaskModal"
                    style="background-color: var(--pulsify-accent, #5F63F2);">
                Add Task
            </button>
        </div>
    </div>

    <form method="get" action="{{ route('tasks.index') }}" class="card mb-3" id="tasks-filter-form">
        <input type="hidden" name="view" value="{{ $filters['view'] }}">
        <div class="card-body py-2">
            <div class="row g-2 align-items-end">
                <div class="col-6 col-md-3">
                    <label class="form-label small text-muted mb-0">Type</label>
                    <select name="type" class="form-select form-select-sm">
                        <option value="">All types</option>
                        @foreach(TaskController::TYPES as $t)
                            <option value="{{ $t }}" @selected($filters['type'] === $t)>{{ $typeLabels[$t] ?? $t }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label small text-muted mb-0">Assigned To</label>
                    <select name="assigned_to" class="form-select form-select-sm">
                        <option value="">Anyone</option>
                        @foreach($teamMembers as $m)
                            <option value="{{ $m->id }}" @selected($filters['assigned_to'] === (string) $m->id)>
                                {{ $m->name ?: $m->email }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small text-muted mb-0">Priority</label>
                    <select name="priority" class="form-select form-select-sm">
                        <option value="">All</option>
                        @foreach(TaskController::PRIORITIES as $p)
                            <option value="{{ $p }}" @selected($filters['priority'] === $p)>{{ $priorityLabels[$p] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2 @if(($filters['view'] ?? 'kanban') !== 'list') d-none @endif" id="filter-status-wrap">
                    <label class="form-label small text-muted mb-0">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="all" @selected(($filters['status'] ?? 'all') === 'all')>All</option>
                        @foreach($kanbanColumns as $sk => $sl)
                            <option value="{{ $sk }}" @selected(($filters['status'] ?? '') === $sk)>{{ $sl }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-2 d-grid">
                    <button type="submit" class="btn btn-sm btn-outline-secondary">Apply filters</button>
                </div>
            </div>
        </div>
    </form>

    @if(($filters['view'] ?? 'kanban') === 'kanban')
        <div class="row g-3 kanban-board" id="kanban-board">
            @foreach($kanbanColumns as $colKey => $colLabel)
                <div class="col-12 col-lg-6 col-xl-3">
                    <div class="kanban-column rounded-3 p-2 h-100" style="background-color: #e9ecef; min-height: 420px;">
                        <div class="d-flex align-items-center justify-content-between px-1 pb-2">
                            <span class="fw-semibold small">{{ $colLabel }}</span>
                            <span class="badge bg-white text-dark border kanban-count" data-column-status="{{ $colKey }}">{{ $statusCounts[$colKey] ?? 0 }}</span>
                        </div>
                        <div class="kanban-drop-zone rounded-2 p-1 min-h-100" data-drop-status="{{ $colKey }}"
                             style="min-height: 320px;">
                            @foreach($tasks->where('status', $colKey) as $task)
                                @include('tasks.partials.card', ['task' => $task])
                            @endforeach
                        </div>
                        <div class="pt-2 px-1">
                            <button type="button" class="btn btn-link btn-sm text-decoration-none p-0 open-create-task"
                                    data-default-status="{{ $colKey }}">
                                + Add task
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                        <tr>
                            <th>Title</th>
                            <th>Type</th>
                            <th>Assigned To</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Due Date</th>
                            <th class="text-end">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($tasks as $task)
                            @php
                                $due = $task->due_date ? $task->due_date->copy()->startOfDay() : null;
                                $overdue = $due && $due->lt($todayStart);
                                $dueToday = $due && $due->equalTo($todayStart);
                            @endphp
                            <tr class="task-row-open" data-task-id="{{ $task->id }}" style="cursor: pointer;">
                                <td class="fw-medium">
                                    <div>{{ $task->title }}</div>
                                    @if(($task->subtasks_count ?? 0) > 0)
                                        <div class="small text-muted">{{ $task->subtasks_count }} subtasks</div>
                                    @endif
                                    @if(($task->checklists_count ?? 0) > 0)
                                        @php
                                            $cp = $task->checklists_count > 0
                                                ? (int) round(($task->completed_checklists_count ?? 0) / $task->checklists_count * 100)
                                                : 0;
                                        @endphp
                                        <div class="progress mt-1" style="max-width: 140px; height: 4px;">
                                            <div class="progress-bar" style="width: {{ $cp }}%; background-color: var(--pulsify-accent, #5F63F2);"></div>
                                        </div>
                                        <span class="small text-muted">{{ (int) ($task->completed_checklists_count ?? 0) }}/{{ (int) ($task->checklists_count ?? 0) }} ✓</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $typeBadgeClass[$task->type] ?? 'bg-light text-dark' }} small fw-normal">
                                        {{ $typeLabels[$task->type] ?? $task->type }}
                                    </span>
                                </td>
                                <td>
                                    @if($task->assignee)
                                        <span class="d-inline-flex align-items-center gap-2">
                                            <span class="avatar-xs rounded-circle bg-pulsify-accent text-white d-inline-flex align-items-center justify-content-center"
                                                  style="width:28px;height:28px;font-size:12px;">
                                                {{ strtoupper(mb_substr($task->assignee->name ?: $task->assignee->email, 0, 1)) }}
                                            </span>
                                            <span class="small">{{ $task->assignee->name ?: $task->assignee->email }}</span>
                                        </span>
                                    @else
                                        <span class="text-muted small">Unassigned</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $priorityBadgeClass[$task->priority] ?? 'bg-light' }}">
                                        {{ $priorityLabels[$task->priority] ?? $task->priority }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge {{ $statusBadgeClass[$task->status] ?? 'bg-light' }}">
                                        {{ $statusLabels[$task->status] ?? $task->status }}
                                    </span>
                                </td>
                                <td>
                                    @if($task->due_date)
                                        <span class="small @if($overdue) text-danger fw-semibold @elseif($dueToday) text-warning @else text-muted @endif">
                                            <i class="ri-calendar-line me-1"></i>{{ $task->due_date->format('M j, Y') }}
                                        </span>
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>
                                <td class="text-end" onclick="event.stopPropagation();">
                                    <button type="button" class="btn btn-sm btn-link text-secondary p-1 open-edit-task" title="Edit"
                                            data-task-id="{{ $task->id }}">
                                        <i class="ri-pencil-line fs-18"></i>
                                    </button>
                                    <form action="{{ route('tasks.destroy', $task) }}" method="post" class="d-inline"
                                          onsubmit="return confirm('Delete this task?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-link text-danger p-1" title="Delete">
                                            <i class="ri-delete-bin-line fs-18"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">No tasks match your filters.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    @include('tasks.partials.create-modal')
    @include('tasks.partials.edit-modal')

    <div class="offcanvas offcanvas-end" tabindex="-1" id="taskDetailPanel" style="width: min(440px, 100vw);"
         aria-labelledby="taskDetailPanelLabel">
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title" id="taskDetailPanelLabel">Task</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body d-flex flex-column">
            <form id="panel-task-form" class="flex-grow-1 d-flex flex-column" onsubmit="return false;">
                @csrf

                <div class="mb-2">
                    <label class="form-label small">Title</label>
                    <input type="text" name="title" id="panel-title" class="form-control form-control-sm" required maxlength="255">
                </div>
                <div class="mb-2">
                    <label class="form-label small">Type</label>
                    <select name="type" id="panel-type" class="form-select form-select-sm">
                        @foreach(TaskController::TYPES as $t)
                            <option value="{{ $t }}">{{ $typeLabels[$t] ?? $t }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-2">
                    <label class="form-label small">Assigned To</label>
                    <select name="assigned_to" id="panel-assigned" class="form-select form-select-sm">
                        <option value="">Unassigned</option>
                        @foreach($teamMembers as $m)
                            <option value="{{ $m->id }}">{{ $m->name ?: $m->email }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-2">
                    <label class="form-label small">Due date</label>
                    <input type="date" name="due_date" id="panel-due" class="form-control form-control-sm">
                </div>
                <div class="mb-2">
                    <label class="form-label small d-block">Priority</label>
                    @foreach(TaskController::PRIORITIES as $p)
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="priority" id="panel-priority-{{ $p }}" value="{{ $p }}">
                            <label class="form-check-label small" for="panel-priority-{{ $p }}">{{ $priorityLabels[$p] }}</label>
                        </div>
                    @endforeach
                </div>
                <div class="mb-2">
                    <label class="form-label small">Status</label>
                    <select name="status" id="panel-status" class="form-select form-select-sm">
                        @foreach($kanbanColumns as $sk => $sl)
                            <option value="{{ $sk }}">{{ $sl }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-2">
                    <label class="form-label small">Link to post <span class="text-muted">(optional)</span></label>
                    @include('tasks.partials.select-post-link', ['selectId' => 'panel-post-id', 'includeName' => false, 'postsForTaskLink' => $postsForTaskLink])
                </div>
                <div class="mb-2">
                    <label class="form-label small">Link to campaign <span class="text-muted">(optional)</span></label>
                    @include('tasks.partials.select-campaign-link', ['selectId' => 'panel-campaign-id', 'includeName' => false, 'campaignsForTaskLink' => $campaignsForTaskLink])
                </div>
                <div class="mb-2 small border rounded p-2 bg-light" id="panel-linked-post-card" style="display: none;">
                    <div class="fw-semibold small mb-1">Linked post</div>
                    <div class="d-flex align-items-center gap-2" id="panel-linked-post-inner"></div>
                </div>
                <div class="mb-2 small border rounded p-2 bg-light" id="panel-linked-campaign-card" style="display: none;">
                    <div class="fw-semibold small mb-1">Linked campaign</div>
                    <div id="panel-linked-campaign-inner"></div>
                </div>
                <div class="mb-3">
                    <button type="button" class="btn btn-sm btn-primary" id="panel-save-task">Save changes</button>
                </div>

                <hr class="my-2">

                <div class="mb-3" id="panel-checklist-section">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-1">
                        <span class="fw-semibold small">Checklist</span>
                        <span class="small text-muted" id="panel-checklist-ratio">0/0</span>
                    </div>
                    <div class="progress mb-2" style="height: 6px;">
                        <div class="progress-bar" id="panel-checklist-progress" role="progressbar" style="width: 0%; background-color: var(--pulsify-accent, #5F63F2);"></div>
                    </div>
                    <div id="panel-checklist-items" class="small mb-2"></div>
                    <div class="input-group input-group-sm">
                        <input type="text" class="form-control" id="panel-new-checklist-title" maxlength="500" placeholder="New checklist item…">
                        <button class="btn btn-outline-secondary" type="button" id="panel-add-checklist-btn" title="Add">+</button>
                    </div>
                    <p class="text-muted small mb-0 mt-2 d-none" id="panel-checklist-empty">No checklist items. Add one above.</p>
                </div>

                <hr class="my-2">

                <div class="mb-3" id="panel-subtasks-section">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="fw-semibold small">Subtasks</span>
                        <span class="badge bg-secondary" id="panel-subtasks-count">0</span>
                    </div>
                    <div id="panel-subtasks-list" class="small mb-2"></div>
                    <div class="border rounded p-2 bg-light">
                        <div class="row g-2 align-items-end">
                            <div class="col-12">
                                <label class="form-label small mb-0">Title</label>
                                <input type="text" class="form-control form-control-sm" id="panel-new-subtask-title" maxlength="255" placeholder="Subtask title">
                            </div>
                            <div class="col-6">
                                <label class="form-label small mb-0">Assign</label>
                                <select class="form-select form-select-sm" id="panel-new-subtask-assignee">
                                    <option value="">—</option>
                                    @foreach($teamMembers as $m)
                                        <option value="{{ $m->id }}">{{ $m->name ?: $m->email }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label small mb-0">Due</label>
                                <input type="date" class="form-control form-control-sm" id="panel-new-subtask-due">
                            </div>
                            <div class="col-12">
                                <button type="button" class="btn btn-sm btn-outline-primary w-100" id="panel-add-subtask-btn">Add subtask</button>
                            </div>
                        </div>
                    </div>
                </div>

                <hr class="my-2">

                <div class="fw-semibold small mb-2">Notes</div>
                <div id="panel-notes-thread" class="small flex-grow-1 overflow-auto mb-3" style="max-height: 220px;"></div>

                <div>
                    <label class="form-label small">Add note</label>
                    <textarea class="form-control form-control-sm mb-2" id="panel-new-note" rows="3" maxlength="2000" placeholder="Write a note…"></textarea>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="panel-add-note">Add Note</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    @php
        $tasksJsonForScript = $tasksJson;
    @endphp
    <script>
        (function () {
            const tasksData = @json($tasksJsonForScript);
            const csrfToken = @json(csrf_token());
            const tasksBaseUrl = @json(url('/tasks'));
            const checklistBaseUrl = @json(url('/tasks/checklist'));
            const subtaskBaseUrl = @json(url('/tasks/subtask'));
            let currentPanelTaskId = null;
            let currentDetail = null;

            function taskUrl(id) {
                return tasksBaseUrl + '/' + encodeURIComponent(id);
            }

            function openDetailPanel(taskId) {
                if (!tasksData[taskId]) {
                    return;
                }
                fetch(taskUrl(taskId) + '/detail', {
                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    credentials: 'same-origin',
                }).then(function (r) {
                    if (!r.ok) {
                        throw new Error();
                    }
                    return r.json();
                }).then(function (detail) {
                    currentDetail = detail;
                    currentPanelTaskId = taskId;
                    applyDetailToPanel(detail);
                    mergeTasksDataFromDetail(taskId, detail);
                    bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('taskDetailPanel')).show();
                }).catch(function () {
                    alert('Could not load task.');
                });
            }

            function mergeTasksDataFromDetail(taskId, d) {
                if (!tasksData[taskId]) {
                    return;
                }
                Object.assign(tasksData[taskId], {
                    post_id: d.post_id,
                    campaign_id: d.campaign_id,
                    checklists_count: d.checklists_total,
                    completed_checklists_count: d.checklists_completed,
                    subtasks_count: (d.subtasks || []).length,
                });
            }

            function applyDetailToPanel(d) {
                document.getElementById('panel-title').value = d.title || '';
                document.getElementById('panel-type').value = d.type;
                document.getElementById('panel-assigned').value = d.assigned_to ? String(d.assigned_to) : '';
                document.getElementById('panel-due').value = d.due_date || '';
                const pr = document.querySelector('#panel-task-form input[name="priority"][value="' + d.priority + '"]');
                if (pr) {
                    pr.checked = true;
                }
                document.getElementById('panel-status').value = d.status;
                const pp = document.getElementById('panel-post-id');
                const pc = document.getElementById('panel-campaign-id');
                if (pp) {
                    pp.value = d.post_id ? String(d.post_id) : '';
                }
                if (pc) {
                    pc.value = d.campaign_id ? String(d.campaign_id) : '';
                }
                renderLinkedPostCard(d);
                renderLinkedCampaignCard(d);
                renderChecklistSection(d);
                renderSubtasksSection(d);
                renderNotesThread(d.notes || []);
            }

            function renderLinkedPostCard(d) {
                const card = document.getElementById('panel-linked-post-card');
                const inner = document.getElementById('panel-linked-post-inner');
                if (!d.post) {
                    card.style.display = 'none';
                    return;
                }
                card.style.display = 'block';
                const st = (d.post.status || '').replace(/_/g, ' ');
                const em = d.post.platform_emoji || '📄';
                inner.innerHTML = '<span class="me-2">' + em + '</span>' +
                    '<span class="fw-medium">' + escapeHtml(d.post.title) + '</span> ' +
                    '<span class="badge bg-secondary small">' + escapeHtml(st) + '</span> ' +
                    '<a href="' + escapeHtml(d.post.edit_url) + '" class="small ms-1">View Post →</a>';
            }

            function renderLinkedCampaignCard(d) {
                const card = document.getElementById('panel-linked-campaign-card');
                const inner = document.getElementById('panel-linked-campaign-inner');
                if (!d.campaign) {
                    card.style.display = 'none';
                    return;
                }
                card.style.display = 'block';
                const st = (d.campaign.status || '').replace(/_/g, ' ');
                inner.innerHTML = '<span class="fw-medium">' + escapeHtml(d.campaign.name) + '</span> ' +
                    '<span class="badge bg-secondary small">' + escapeHtml(st) + '</span>' +
                    ' <span class="text-muted small">(Campaign link — detail page coming soon)</span>';
            }

            function updateChecklistProgressUI(d) {
                const total = d.checklists_total || 0;
                const done = d.checklists_completed || 0;
                const pct = total > 0 ? Math.round(done / total * 100) : 0;
                document.getElementById('panel-checklist-ratio').textContent = done + '/' + total + ' complete';
                const bar = document.getElementById('panel-checklist-progress');
                bar.style.width = pct + '%';
                bar.setAttribute('aria-valuenow', String(pct));
                const empty = document.getElementById('panel-checklist-empty');
                if (total === 0) {
                    empty.classList.remove('d-none');
                } else {
                    empty.classList.add('d-none');
                }
            }

            function renderChecklistSection(d) {
                updateChecklistProgressUI(d);
                const wrap = document.getElementById('panel-checklist-items');
                const items = d.checklists || [];
                if (!items.length) {
                    wrap.innerHTML = '';
                    return;
                }
                wrap.innerHTML = items.map(function (c) {
                    const meta = c.is_completed && c.completed_by_name
                        ? '<div class="text-muted" style="font-size:11px;">' + escapeHtml(c.completed_by_name) + ' · ' +
                        (c.completed_at ? escapeHtml(new Date(c.completed_at).toLocaleString()) : '') + '</div>'
                        : '';
                    const strike = c.is_completed ? ' text-decoration-line-through text-muted' : '';
                    return '<div class="d-flex align-items-start gap-2 py-1 border-bottom border-light checklist-row" data-checklist-id="' + c.id + '">' +
                        '<input type="checkbox" class="form-check-input mt-1 chk-toggle" data-checklist-id="' + c.id + '" ' + (c.is_completed ? 'checked' : '') + '>' +
                        '<div class="flex-grow-1 min-w-0">' +
                        '<div class="chk-title' + strike + '">' + escapeHtml(c.title) + '</div>' + meta + '</div>' +
                        '<button type="button" class="btn btn-sm btn-link text-danger p-0 chk-del" data-checklist-id="' + c.id + '" title="Remove">×</button></div>';
                }).join('');
            }

            function renderSubtasksSection(d) {
                const list = document.getElementById('panel-subtasks-list');
                const badge = document.getElementById('panel-subtasks-count');
                const items = d.subtasks || [];
                badge.textContent = String(items.length);
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                if (!items.length) {
                    list.innerHTML = '<p class="text-muted small mb-0">No subtasks yet.</p>';
                    return;
                }
                list.innerHTML = items.map(function (s) {
                    let dueClass = 'text-muted';
                    if (s.due_date) {
                        const dd = new Date(s.due_date + 'T12:00:00');
                        if (dd < today) {
                            dueClass = 'text-danger fw-semibold';
                        }
                    }
                    const av = s.assignee_name ? s.assignee_name.charAt(0).toUpperCase() : '—';
                    const dueTxt = s.due_date ? new Date(s.due_date + 'T12:00:00').toLocaleDateString() : '—';
                    return '<div class="border rounded p-2 mb-2 subtask-row" data-subtask-id="' + s.id + '">' +
                        '<div class="d-flex align-items-start gap-2">' +
                        '<button type="button" class="btn btn-sm btn-outline-secondary flex-shrink-0 sub-cycle" data-subtask-id="' + s.id + '" title="Cycle status">' +
                        escapeHtml(s.status.replace(/_/g, ' ')) + '</button>' +
                        '<div class="flex-grow-1 min-w-0"><div class="fw-medium small">' + escapeHtml(s.title) + '</div>' +
                        '<div class="d-flex flex-wrap align-items-center gap-2 mt-1">' +
                        '<span class="rounded-circle bg-secondary text-white d-inline-flex align-items-center justify-content-center" style="width:22px;height:22px;font-size:10px;">' + escapeHtml(av) + '</span>' +
                        '<span class="small text-muted">' + escapeHtml(s.assignee_name || 'Unassigned') + '</span>' +
                        '<span class="small ' + dueClass + '"><i class="ri-calendar-line"></i> ' + escapeHtml(dueTxt) + '</span></div></div>' +
                        '<button type="button" class="btn btn-sm btn-link text-danger p-0 sub-del" data-subtask-id="' + s.id + '">×</button></div></div>';
                }).join('');
            }

            function renderNotesThread(notes) {
                const wrap = document.getElementById('panel-notes-thread');
                if (!notes || !notes.length) {
                    wrap.innerHTML = '<p class="text-muted mb-0">No notes yet.</p>';
                    return;
                }
                wrap.innerHTML = notes.map(function (n) {
                    const when = n.created_at ? new Date(n.created_at).toLocaleString() : '';
                    const initial = (n.user_name || 'U').charAt(0).toUpperCase();
                    return '<div class="d-flex gap-2 mb-3 pb-2 border-bottom border-light">' +
                        '<span class="flex-shrink-0 rounded-circle bg-secondary text-white d-inline-flex align-items-center justify-content-center" style="width:32px;height:32px;font-size:13px;">' + initial + '</span>' +
                        '<div class="min-w-0">' +
                        '<div class="fw-semibold">' + escapeHtml(n.user_name || 'User') + ' <span class="text-muted fw-normal">' + escapeHtml(when) + '</span></div>' +
                        '<div class="text-break">' + escapeHtml(n.note) + '</div>' +
                        '</div></div>';
                }).join('');
            }

            function escapeHtml(s) {
                const d = document.createElement('div');
                d.textContent = s;
                return d.innerHTML;
            }

            document.querySelectorAll('.task-row-open').forEach(function (row) {
                row.addEventListener('click', function () {
                    openDetailPanel(parseInt(row.getAttribute('data-task-id'), 10));
                });
            });

            document.querySelectorAll('.task-card').forEach(function (card) {
                card.addEventListener('click', function (e) {
                    if (e.target.closest('.task-card-actions')) {
                        return;
                    }
                    openDetailPanel(parseInt(card.getAttribute('data-task-id'), 10));
                });
            });

            document.querySelectorAll('.open-create-task').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const st = btn.getAttribute('data-default-status');
                    const sel = document.querySelector('#createTaskModal select[name="status"]');
                    if (sel && st) {
                        sel.value = st;
                    }
                    bootstrap.Modal.getOrCreateInstance(document.getElementById('createTaskModal')).show();
                });
            });

            document.querySelectorAll('.open-edit-task').forEach(function (btn) {
                btn.addEventListener('click', function (e) {
                    e.stopPropagation();
                    const id = parseInt(btn.getAttribute('data-task-id'), 10);
                    fillEditModal(id);
                    bootstrap.Modal.getOrCreateInstance(document.getElementById('editTaskModal')).show();
                });
            });

            function fillEditModal(taskId) {
                const t = tasksData[taskId];
                if (!t) {
                    return;
                }
                const form = document.getElementById('edit-task-form');
                form.action = taskUrl(taskId);
                form.querySelector('[name="title"]').value = t.title || '';
                form.querySelector('[name="type"]').value = t.type;
                form.querySelector('[name="assigned_to"]').value = t.assigned_to ? String(t.assigned_to) : '';
                form.querySelector('[name="due_date"]').value = t.due_date || '';
                form.querySelectorAll('[name="priority"]').forEach(function (r) {
                    r.checked = r.value === t.priority;
                });
                form.querySelector('[name="status"]').value = t.status;
                const ep = document.getElementById('edit-post-id');
                const ec = document.getElementById('edit-campaign-id');
                if (ep) {
                    ep.value = t.post_id ? String(t.post_id) : '';
                }
                if (ec) {
                    ec.value = t.campaign_id ? String(t.campaign_id) : '';
                }
            }

            const panelSaveBtn = document.getElementById('panel-save-task');
            if (panelSaveBtn) {
                panelSaveBtn.addEventListener('click', function () {
                    if (!currentPanelTaskId) {
                        return;
                    }
                    const assignedVal = document.getElementById('panel-assigned').value;
                    const dueVal = document.getElementById('panel-due').value;
                    const priorityEl = document.querySelector('#panel-task-form input[name="priority"]:checked');
                    if (!priorityEl) {
                        alert('Select a priority.');
                        return;
                    }
                    const postSel = document.getElementById('panel-post-id');
                    const campSel = document.getElementById('panel-campaign-id');
                    const payload = {
                        title: document.getElementById('panel-title').value,
                        type: document.getElementById('panel-type').value,
                        assigned_to: assignedVal === '' ? null : parseInt(assignedVal, 10),
                        due_date: dueVal === '' ? null : dueVal,
                        priority: priorityEl.value,
                        status: document.getElementById('panel-status').value,
                        post_id: postSel && postSel.value ? parseInt(postSel.value, 10) : null,
                        campaign_id: campSel && campSel.value ? parseInt(campSel.value, 10) : null,
                    };
                    fetch(taskUrl(currentPanelTaskId), {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: JSON.stringify(payload),
                    }).then(function (r) {
                        if (!r.ok) {
                            throw new Error();
                        }
                        return r.json();
                    }).then(function () {
                        window.location.reload();
                    }).catch(function () {
                        alert('Could not save task.');
                    });
                });
            }

            const panelAddNoteBtn = document.getElementById('panel-add-note');
            if (panelAddNoteBtn) {
                panelAddNoteBtn.addEventListener('click', function () {
                const ta = document.getElementById('panel-new-note');
                const text = (ta.value || '').trim();
                if (!currentPanelTaskId || !text) {
                    return;
                }
                fetch(taskUrl(currentPanelTaskId) + '/notes', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({ note: text }),
                }).then(function (r) {
                    if (!r.ok) {
                        throw new Error();
                    }
                    return r.json();
                }).then(function (data) {
                    ta.value = '';
                    if (!tasksData[currentPanelTaskId].notes) {
                        tasksData[currentPanelTaskId].notes = [];
                    }
                    tasksData[currentPanelTaskId].notes.push(data.note);
                    tasksData[currentPanelTaskId].task_notes_count = (tasksData[currentPanelTaskId].task_notes_count || 0) + 1;
                    if (currentDetail && currentDetail.id === currentPanelTaskId) {
                        if (!currentDetail.notes) {
                            currentDetail.notes = [];
                        }
                        currentDetail.notes.push({
                            id: data.note.id,
                            note: data.note.note,
                            user_name: data.note.user_name,
                            created_at: data.note.created_at,
                        });
                        renderNotesThread(currentDetail.notes);
                    } else {
                        renderNotesThread(tasksData[currentPanelTaskId].notes);
                    }
                    updateCardNoteCount(currentPanelTaskId, tasksData[currentPanelTaskId].task_notes_count);
                }).catch(function () {
                    alert('Could not add note.');
                });
                });
            }

            function updateCardNoteCount(taskId, count) {
                document.querySelectorAll('.task-card[data-task-id="' + taskId + '"] .task-notes-meta').forEach(function (meta) {
                    const el = meta.querySelector('.task-note-count');
                    if (el) {
                        el.textContent = String(count);
                    }
                    if (count > 0) {
                        meta.classList.remove('d-none');
                    } else {
                        meta.classList.add('d-none');
                    }
                });
            }

            function addChecklistItem() {
                const inp = document.getElementById('panel-new-checklist-title');
                const t = (inp.value || '').trim();
                if (!currentPanelTaskId || !t) {
                    return;
                }
                fetch(taskUrl(currentPanelTaskId) + '/checklist', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({ items: [{ title: t }] }),
                }).then(function (r) {
                    if (!r.ok) {
                        throw new Error();
                    }
                    return r.json();
                }).then(function (data) {
                    inp.value = '';
                    if (!currentDetail.checklists) {
                        currentDetail.checklists = [];
                    }
                    (data.checklists || []).forEach(function (c) {
                        currentDetail.checklists.push(c);
                    });
                    currentDetail.checklists_total = currentDetail.checklists.length;
                    currentDetail.checklists_completed = currentDetail.checklists.filter(function (x) {
                        return x.is_completed;
                    }).length;
                    renderChecklistSection(currentDetail);
                    mergeTasksDataFromDetail(currentPanelTaskId, currentDetail);
                }).catch(function () {
                    alert('Could not add checklist item.');
                });
            }

            document.getElementById('panel-checklist-items').addEventListener('change', function (e) {
                const t = e.target;
                if (!t.classList.contains('chk-toggle')) {
                    return;
                }
                const id = parseInt(t.getAttribute('data-checklist-id'), 10);
                fetch(checklistBaseUrl + '/' + encodeURIComponent(id) + '/toggle', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({}),
                }).then(function (r) {
                    if (!r.ok) {
                        throw new Error();
                    }
                    return r.json();
                }).then(function (data) {
                    const idx = currentDetail.checklists.findIndex(function (x) {
                        return Number(x.id) === Number(data.checklist.id);
                    });
                    if (idx >= 0) {
                        currentDetail.checklists[idx] = data.checklist;
                    }
                    currentDetail.checklists_completed = currentDetail.checklists.filter(function (x) {
                        return x.is_completed;
                    }).length;
                    renderChecklistSection(currentDetail);
                    mergeTasksDataFromDetail(currentPanelTaskId, currentDetail);
                }).catch(function () {
                    t.checked = !t.checked;
                    alert('Could not update checklist.');
                });
            });

            document.getElementById('panel-checklist-items').addEventListener('click', function (e) {
                const btn = e.target.closest('.chk-del');
                if (!btn) {
                    return;
                }
                const id = parseInt(btn.getAttribute('data-checklist-id'), 10);
                fetch(checklistBaseUrl + '/' + encodeURIComponent(id), {
                    method: 'DELETE',
                    headers: {
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                }).then(function (r) {
                    if (!r.ok) {
                        throw new Error();
                    }
                    return r.json();
                }).then(function () {
                    currentDetail.checklists = currentDetail.checklists.filter(function (x) {
                        return x.id !== id;
                    });
                    currentDetail.checklists_total = currentDetail.checklists.length;
                    currentDetail.checklists_completed = currentDetail.checklists.filter(function (x) {
                        return x.is_completed;
                    }).length;
                    renderChecklistSection(currentDetail);
                    mergeTasksDataFromDetail(currentPanelTaskId, currentDetail);
                }).catch(function () {
                    alert('Could not delete item.');
                });
            });

            document.getElementById('panel-add-checklist-btn').addEventListener('click', addChecklistItem);
            document.getElementById('panel-new-checklist-title').addEventListener('keydown', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    addChecklistItem();
                }
            });

            document.getElementById('panel-subtasks-list').addEventListener('click', function (e) {
                const del = e.target.closest('.sub-del');
                if (del) {
                    const id = parseInt(del.getAttribute('data-subtask-id'), 10);
                    fetch(subtaskBaseUrl + '/' + encodeURIComponent(id), {
                        method: 'DELETE',
                        headers: {
                            Accept: 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    }).then(function (r) {
                        if (!r.ok) {
                            throw new Error();
                        }
                        return r.json();
                    }).then(function () {
                        currentDetail.subtasks = currentDetail.subtasks.filter(function (x) {
                            return x.id !== id;
                        });
                        renderSubtasksSection(currentDetail);
                        mergeTasksDataFromDetail(currentPanelTaskId, currentDetail);
                    }).catch(function () {
                        alert('Could not delete subtask.');
                    });
                    return;
                }
                const cyc = e.target.closest('.sub-cycle');
                if (!cyc) {
                    return;
                }
                const id = parseInt(cyc.getAttribute('data-subtask-id'), 10);
                const s = currentDetail.subtasks.find(function (x) {
                    return x.id === id;
                });
                if (!s) {
                    return;
                }
                const order = ['todo', 'in_progress', 'done'];
                const next = order[(order.indexOf(s.status) + 1) % 3];
                fetch(subtaskBaseUrl + '/' + encodeURIComponent(id), {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({
                        title: s.title,
                        assigned_to: s.assigned_to,
                        due_date: s.due_date,
                        status: next,
                    }),
                }).then(function (r) {
                    if (!r.ok) {
                        throw new Error();
                    }
                    return r.json();
                }).then(function (data) {
                    const idx = currentDetail.subtasks.findIndex(function (x) {
                        return x.id === id;
                    });
                    if (idx >= 0) {
                        currentDetail.subtasks[idx] = data.subtask;
                    }
                    renderSubtasksSection(currentDetail);
                    mergeTasksDataFromDetail(currentPanelTaskId, currentDetail);
                }).catch(function () {
                    alert('Could not update subtask.');
                });
            });

            document.getElementById('panel-add-subtask-btn').addEventListener('click', function () {
                const title = (document.getElementById('panel-new-subtask-title').value || '').trim();
                if (!currentPanelTaskId || !title) {
                    return;
                }
                const asg = document.getElementById('panel-new-subtask-assignee').value;
                const due = document.getElementById('panel-new-subtask-due').value;
                fetch(taskUrl(currentPanelTaskId) + '/subtask', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({
                        title: title,
                        assigned_to: asg === '' ? null : parseInt(asg, 10),
                        due_date: due === '' ? null : due,
                        status: 'todo',
                    }),
                }).then(function (r) {
                    if (!r.ok) {
                        throw new Error();
                    }
                    return r.json();
                }).then(function (data) {
                    document.getElementById('panel-new-subtask-title').value = '';
                    document.getElementById('panel-new-subtask-assignee').value = '';
                    document.getElementById('panel-new-subtask-due').value = '';
                    if (!currentDetail.subtasks) {
                        currentDetail.subtasks = [];
                    }
                    currentDetail.subtasks.push(data.subtask);
                    renderSubtasksSection(currentDetail);
                    mergeTasksDataFromDetail(currentPanelTaskId, currentDetail);
                }).catch(function () {
                    alert('Could not add subtask.');
                });
            });

            /* Kanban HTML5 drag and drop */
            let draggedId = null;
            document.querySelectorAll('.task-card').forEach(function (card) {
                card.addEventListener('dragstart', function (e) {
                    draggedId = parseInt(card.getAttribute('data-task-id'), 10);
                    e.dataTransfer.setData('text/plain', String(draggedId));
                    e.dataTransfer.effectAllowed = 'move';
                    card.classList.add('opacity-50');
                });
                card.addEventListener('dragend', function () {
                    card.classList.remove('opacity-50');
                    draggedId = null;
                });
            });

            document.querySelectorAll('.kanban-drop-zone').forEach(function (zone) {
                zone.addEventListener('dragover', function (e) {
                    e.preventDefault();
                    e.dataTransfer.dropEffect = 'move';
                });
                zone.addEventListener('drop', function (e) {
                    e.preventDefault();
                    const id = parseInt(e.dataTransfer.getData('text/plain'), 10);
                    const newStatus = zone.getAttribute('data-drop-status');
                    if (!id || !newStatus || !tasksData[id]) {
                        return;
                    }
                    if (tasksData[id].status === newStatus) {
                        return;
                    }
                    const card = document.querySelector('.task-card[data-task-id="' + id + '"]');
                    if (!card) {
                        return;
                    }
                    fetch(taskUrl(id) + '/status', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: JSON.stringify({ status: newStatus }),
                    }).then(function (r) {
                        if (!r.ok) {
                            throw new Error();
                        }
                        return r.json();
                    }).then(function () {
                        const oldStatus = tasksData[id].status;
                        tasksData[id].status = newStatus;
                        zone.appendChild(card);
                        card.setAttribute('data-status', newStatus);
                        adjustKanbanCount(oldStatus, -1);
                        adjustKanbanCount(newStatus, 1);
                    }).catch(function () {
                        alert('Could not update status.');
                    });
                });
            });

            function adjustKanbanCount(status, delta) {
                const badge = document.querySelector('.kanban-count[data-column-status="' + status + '"]');
                if (badge) {
                    const n = Math.max(0, parseInt(badge.textContent, 10) + delta);
                    badge.textContent = String(n);
                }
            }
        })();
    </script>
@endsection
