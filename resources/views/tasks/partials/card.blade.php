@php
    $due = $task->due_date ? $task->due_date->copy()->startOfDay() : null;
    $overdue = $due && $due->lt($todayStart);
    $dueToday = $due && $due->equalTo($todayStart);
    $border = $priorityBorder[$task->priority] ?? '#adb5bd';
    $cc = (int) ($task->checklists_count ?? 0);
    $ccDone = (int) ($task->completed_checklists_count ?? 0);
    $subCount = (int) ($task->subtasks_count ?? 0);
    $chkPct = $cc > 0 ? (int) round($ccDone / $cc * 100) : 0;
@endphp
<div class="task-card position-relative mb-2 p-3 bg-white rounded-2 shadow-sm"
     style="border-radius: 8px; padding: 12px; margin-bottom: 8px; border-left: 4px solid {{ $border }}; cursor: pointer;"
     draggable="true"
     data-task-id="{{ $task->id }}"
     data-status="{{ $task->status }}">
    <div class="task-card-actions position-absolute top-0 end-0 p-1 opacity-0 task-card-hover-actions" style="transition: opacity .15s;">
        <button type="button" class="btn btn-sm btn-link text-secondary p-0 me-1 open-edit-task" title="Edit"
                data-task-id="{{ $task->id }}">
            <i class="ri-pencil-line"></i>
        </button>
        <form action="{{ route('tasks.destroy', $task) }}" method="post" class="d-inline"
              onsubmit="event.stopPropagation(); return confirm('Delete this task?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-sm btn-link text-danger p-0" title="Delete">
                <i class="ri-delete-bin-line"></i>
            </button>
        </form>
    </div>
    <div class="pe-4">
        <div class="fw-semibold mb-1" style="font-size: 14px;">{{ $task->title }}</div>
        @if($subCount > 0)
            <div class="small text-muted mb-1">{{ $subCount }} subtasks</div>
        @endif
        <div class="mb-2">
            <span class="badge {{ $typeBadgeClass[$task->type] ?? 'bg-light text-dark' }} small fw-normal">
                {{ $typeLabels[$task->type] ?? $task->type }}
            </span>
        </div>
        <div class="d-flex align-items-center gap-2 mb-1">
            @if($task->assignee)
                <span class="rounded-circle bg-pulsify-accent text-white d-inline-flex align-items-center justify-content-center flex-shrink-0"
                      style="width: 26px; height: 26px; font-size: 11px;">
                    {{ strtoupper(mb_substr($task->assignee->name ?: $task->assignee->email, 0, 1)) }}
                </span>
                <span style="font-size: 13px;">{{ $task->assignee->name ?: $task->assignee->email }}</span>
            @else
                <span class="text-muted small">Unassigned</span>
            @endif
        </div>
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-1">
            @if($task->due_date)
                <span class="small @if($overdue) text-danger fw-semibold @elseif($dueToday) text-warning @else text-muted @endif">
                    <i class="ri-calendar-line me-1"></i>{{ $task->due_date->format('M j, Y') }}
                </span>
            @else
                <span class="text-muted small">No due date</span>
            @endif
            <span class="task-notes-meta small text-muted @if(($task->task_notes_count ?? 0) === 0) d-none @endif">
                <i class="ri-chat-3-line me-1"></i><span class="task-note-count">{{ (int) ($task->task_notes_count ?? 0) }}</span>
            </span>
        </div>
        @if($cc > 0)
            <div class="mt-2">
                <div class="progress" style="height: 4px;">
                    <div class="progress-bar" role="progressbar" style="width: {{ $chkPct }}%; background-color: var(--pulsify-accent, #5F63F2);"></div>
                </div>
                <div class="small text-muted mt-1">{{ $ccDone }}/{{ $cc }} ✓</div>
            </div>
        @endif
        @if($task->post_id || $task->campaign_id)
            <div class="d-flex align-items-center gap-2 mt-2 pt-1 border-top border-light">
                @if($task->post_id && $task->post?->channel)
                    <div class="flex-shrink-0" style="line-height:0;" title="Linked post">
                        @include('calendar.partials.platform-icon', ['platform' => $task->post->channel->platform ?? 'custom', 'suffix' => 'tc-'.$task->id, 'size' => 16])
                    </div>
                @endif
                @if($task->campaign_id)
                    <span class="small text-muted" title="Linked campaign: {{ $task->campaign?->name }}">🎯</span>
                @endif
            </div>
        @endif
    </div>
</div>
