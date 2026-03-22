@php
    use App\Http\Controllers\TaskController;
    use App\Http\Controllers\ChannelController;

    $typeLabels = $typeLabels ?? [
        'content' => 'Content Creation',
        'shooting' => 'Shooting',
        'editing' => 'Editing',
        'design' => 'Design',
        'publishing' => 'Publishing',
        'campaign' => 'Campaign',
        'general' => 'General',
    ];
    $kanbanColumns = $kanbanColumns ?? [
        'todo' => 'To Do',
        'in_progress' => 'In Progress',
        'in_review' => 'In Review',
        'done' => 'Done',
    ];
    $priorityLabels = $priorityLabels ?? ['high' => 'High', 'medium' => 'Medium', 'low' => 'Low'];
    $platformOptions = $platformOptions ?? ChannelController::platformOptions();
@endphp
<div class="modal fade" id="editTaskModal" tabindex="-1" aria-labelledby="editTaskModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form method="post" id="edit-task-form" action="#">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title" id="editTaskModalLabel">Edit task</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" required maxlength="255">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Type <span class="text-danger">*</span></label>
                        <select name="type" class="form-select" required>
                            @foreach(TaskController::TYPES as $t)
                                <option value="{{ $t }}">{{ $typeLabels[$t] ?? $t }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Link to a post <span class="text-muted fw-normal">(optional)</span></label>
                        <select name="post_id" id="edit-post-id" class="form-select">
                            <option value="">— None —</option>
                            @foreach($postsForTaskLink->groupBy('status') as $grpStatus => $grpPosts)
                                <optgroup label="{{ ucwords(str_replace('_', ' ', $grpStatus)) }}">
                                    @foreach($grpPosts as $p)
                                        @php
                                            $plat = $p->channel?->platform ?? 'custom';
                                            $platLabel = $platformOptions[$plat] ?? $plat;
                                            $emoji = preg_match('/^(\X)/u', $platLabel, $m) ? $m[1] : '📄';
                                            $line = $p->title ?: (\Illuminate\Support\Str::limit((string) ($p->caption_en ?? ''), 50) ?: 'Untitled');
                                        @endphp
                                        <option value="{{ $p->id }}">{{ $emoji }} {{ $line }}</option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Link to a campaign <span class="text-muted fw-normal">(optional)</span></label>
                        <select name="campaign_id" id="edit-campaign-id" class="form-select">
                            <option value="">— None —</option>
                            @foreach($campaignsForTaskLink as $c)
                                <option value="{{ $c->id }}">{{ $c->name }} — {{ ucwords(str_replace('_', ' ', (string) $c->status)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Assigned To</label>
                        <select name="assigned_to" class="form-select">
                            <option value="">Unassigned</option>
                            @foreach($teamMembers as $m)
                                <option value="{{ $m->id }}">{{ $m->name ?: $m->email }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Due date</label>
                        <input type="date" name="due_date" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label d-block">Priority <span class="text-danger">*</span></label>
                        @foreach(TaskController::PRIORITIES as $p)
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="priority" id="edit-priority-{{ $p }}" value="{{ $p }}">
                                <label class="form-check-label" for="edit-priority-{{ $p }}">{{ $priorityLabels[$p] }}</label>
                            </div>
                        @endforeach
                    </div>
                    <div class="mb-0">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            @foreach($kanbanColumns as $sk => $sl)
                                <option value="{{ $sk }}">{{ $sl }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn text-white" style="background-color: var(--pulsify-accent, #5F63F2);">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
