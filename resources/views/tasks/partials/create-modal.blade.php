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
    $prefillPostId = $prefillPostId ?? null;
    $prefillCampaignId = $prefillCampaignId ?? null;
@endphp
<div class="modal fade" id="createTaskModal" tabindex="-1" aria-labelledby="createTaskModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form method="post" action="{{ route('tasks.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="createTaskModalLabel">Create task</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                               value="{{ old('title') }}" required maxlength="255">
                        @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Type <span class="text-danger">*</span></label>
                        <select name="type" class="form-select @error('type') is-invalid @enderror" required>
                            @foreach(TaskController::TYPES as $t)
                                <option value="{{ $t }}" @selected(old('type', 'general') === $t)>{{ $typeLabels[$t] ?? $t }}</option>
                            @endforeach
                        </select>
                        @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Link to a post <span class="text-muted fw-normal">(optional)</span></label>
                        <select name="post_id" class="form-select @error('post_id') is-invalid @enderror">
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
                                        <option value="{{ $p->id }}" @selected(old('post_id', $prefillPostId) == $p->id)>
                                            {{ $emoji }} {{ $line }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                        @error('post_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Link to a campaign <span class="text-muted fw-normal">(optional)</span></label>
                        <select name="campaign_id" class="form-select @error('campaign_id') is-invalid @enderror">
                            <option value="">— None —</option>
                            @foreach($campaignsForTaskLink as $c)
                                <option value="{{ $c->id }}" @selected(old('campaign_id', $prefillCampaignId) == $c->id)>
                                    {{ $c->name }} — {{ ucwords(str_replace('_', ' ', (string) $c->status)) }}
                                </option>
                            @endforeach
                        </select>
                        @error('campaign_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Assigned To</label>
                        <select name="assigned_to" class="form-select @error('assigned_to') is-invalid @enderror">
                            <option value="">Unassigned</option>
                            @foreach($teamMembers as $m)
                                <option value="{{ $m->id }}" @selected(old('assigned_to') == $m->id)>{{ $m->name ?: $m->email }}</option>
                            @endforeach
                        </select>
                        @error('assigned_to')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Due date</label>
                        <input type="date" name="due_date" class="form-control @error('due_date') is-invalid @enderror" value="{{ old('due_date') }}">
                        @error('due_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label d-block">Priority <span class="text-danger">*</span></label>
                        @foreach(TaskController::PRIORITIES as $p)
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="priority" id="create-priority-{{ $p }}"
                                       value="{{ $p }}" @checked(old('priority', 'medium') === $p)>
                                <label class="form-check-label" for="create-priority-{{ $p }}">{{ $priorityLabels[$p] }}</label>
                            </div>
                        @endforeach
                        @error('priority')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select @error('status') is-invalid @enderror">
                            @foreach($kanbanColumns as $sk => $sl)
                                <option value="{{ $sk }}" @selected(old('status', 'todo') === $sk)>{{ $sl }}</option>
                            @endforeach
                        </select>
                        @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-0">
                        <label class="form-label">Notes <span class="text-muted fw-normal">(optional)</span></label>
                        <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="3" maxlength="2000"
                                  placeholder="Initial note…">{{ old('notes') }}</textarea>
                        @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn text-white" style="background-color: var(--pulsify-accent, #5F63F2);">Create Task</button>
                </div>
            </form>
        </div>
    </div>
</div>
