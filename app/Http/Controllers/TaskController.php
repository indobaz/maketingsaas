<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\Post;
use App\Models\Subtask;
use App\Models\Task;
use App\Models\TaskChecklist;
use App\Models\TaskNote;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TaskController extends Controller
{
    /** @var list<string> */
    public const TYPES = ['content', 'shooting', 'editing', 'design', 'publishing', 'campaign', 'general'];

    /** @var list<string> */
    public const STATUSES = ['todo', 'in_progress', 'in_review', 'done'];

    /** @var list<string> */
    public const PRIORITIES = ['high', 'medium', 'low'];

    /** @var list<string> */
    public const SUBTASK_STATUSES = ['todo', 'in_progress', 'done'];

    public function index(Request $request): View
    {
        $companyId = (int) Auth::user()->company_id;

        $view = $request->query('view', 'kanban');
        if (! in_array($view, ['kanban', 'list'], true)) {
            $view = 'kanban';
        }

        $type = $request->query('type');
        $assignedTo = $request->query('assigned_to');
        $priority = $request->query('priority');
        $statusFilter = $request->query('status');

        $teamMembers = User::query()
            ->where('company_id', $companyId)
            ->orderByRaw('COALESCE(name, email) asc')
            ->get();

        $postsForTaskLink = Post::query()
            ->where('company_id', $companyId)
            ->where('status', '!=', 'published')
            ->with('channel')
            ->orderByRaw("FIELD(status, 'draft','in_review','approved','scheduled','rejected')")
            ->orderByDesc('updated_at')
            ->get();

        $campaignsForTaskLink = Campaign::query()
            ->where('company_id', $companyId)
            ->orderBy('name')
            ->get();

        $baseQuery = Task::query()
            ->where('company_id', $companyId)
            ->with(['assignee', 'creator', 'taskNotes.user', 'post.channel', 'campaign'])
            ->withCount('taskNotes')
            ->withCount([
                'checklists',
                'checklists as completed_checklists_count' => fn ($q) => $q->where('is_completed', true),
                'subtasks',
            ]);

        if (is_string($type) && $type !== '' && in_array($type, self::TYPES, true)) {
            $baseQuery->where('type', $type);
        }

        if ($assignedTo !== null && $assignedTo !== '') {
            $aid = (int) $assignedTo;
            if (User::query()->where('company_id', $companyId)->whereKey($aid)->exists()) {
                $baseQuery->where('assigned_to', $aid);
            }
        }

        if (is_string($priority) && $priority !== '' && in_array($priority, self::PRIORITIES, true)) {
            $baseQuery->where('priority', $priority);
        }

        $statusCounts = [];
        foreach (self::STATUSES as $st) {
            $statusCounts[$st] = (clone $baseQuery)->where('status', $st)->count();
        }

        if ($view === 'list' && is_string($statusFilter) && $statusFilter !== '' && $statusFilter !== 'all' && in_array($statusFilter, self::STATUSES, true)) {
            $baseQuery->where('status', $statusFilter);
        }

        $tasks = $baseQuery
            ->orderByRaw("FIELD(status, 'todo','in_progress','in_review','done')")
            ->orderByRaw('due_date IS NULL')
            ->orderBy('due_date')
            ->orderByDesc('updated_at')
            ->get();

        $filters = [
            'view' => $view,
            'type' => is_string($type) ? $type : '',
            'assigned_to' => $assignedTo !== null && $assignedTo !== '' ? (string) $assignedTo : '',
            'priority' => is_string($priority) ? $priority : '',
            'status' => is_string($statusFilter) ? $statusFilter : 'all',
        ];

        $tasksJson = $tasks->mapWithKeys(fn (Task $t) => [$t->id => $this->taskSummaryForJson($t)])->all();

        return view('tasks.index', [
            'tasks' => $tasks,
            'teamMembers' => $teamMembers,
            'filters' => $filters,
            'statusCounts' => $statusCounts,
            'tasksJson' => $tasksJson,
            'postsForTaskLink' => $postsForTaskLink,
            'campaignsForTaskLink' => $campaignsForTaskLink,
        ]);
    }

    public function getTaskDetail(Task $task): JsonResponse
    {
        $this->assertTaskInCompany($task);

        $task->load([
            'assignee',
            'creator',
            'checklists.completedBy',
            'subtasks.assignee',
            'post.channel',
            'campaign',
            'taskNotes.user',
        ]);

        return response()->json($this->taskDetailPayload($task));
    }

    public function addChecklist(Request $request, Task $task): JsonResponse
    {
        $this->assertTaskInCompany($task);

        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.title' => ['required', 'string', 'max:500'],
        ]);

        $companyId = (int) $task->company_id;
        $maxOrder = (int) $task->checklists()->max('sort_order');
        $created = [];

        foreach ($validated['items'] as $i => $item) {
            $maxOrder++;
            $row = TaskChecklist::create([
                'task_id' => $task->id,
                'company_id' => $companyId,
                'title' => $item['title'],
                'is_completed' => false,
                'sort_order' => $maxOrder,
            ]);
            $created[] = $this->serializeChecklist($row->fresh('completedBy'));
        }

        return response()->json([
            'success' => true,
            'checklists' => $created,
        ]);
    }

    public function toggleChecklist(Request $request, TaskChecklist $checklist): JsonResponse
    {
        $this->assertChecklistInCompany($checklist);

        $checklist->is_completed = ! $checklist->is_completed;
        if ($checklist->is_completed) {
            $checklist->completed_by = Auth::id();
            $checklist->completed_at = now();
        } else {
            $checklist->completed_by = null;
            $checklist->completed_at = null;
        }
        $checklist->save();
        $checklist->load('completedBy');

        $taskId = $checklist->task_id;
        $total = TaskChecklist::query()->where('task_id', $taskId)->count();
        $completed = TaskChecklist::query()->where('task_id', $taskId)->where('is_completed', true)->count();
        $progress = $total > 0 ? (int) round($completed / $total * 100) : 0;

        return response()->json([
            'success' => true,
            'is_completed' => $checklist->is_completed,
            'progress' => $progress,
            'checklist' => $this->serializeChecklist($checklist),
        ]);
    }

    public function deleteChecklist(TaskChecklist $checklist): JsonResponse
    {
        $this->assertChecklistInCompany($checklist);
        $checklist->delete();

        return response()->json(['success' => true]);
    }

    public function addSubtask(Request $request, Task $task): JsonResponse
    {
        $this->assertTaskInCompany($task);

        $companyId = (int) Auth::user()->company_id;
        $request->mergeIfMissing(['status' => 'todo']);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'assigned_to' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id')->where(fn ($q) => $q->where('company_id', $companyId)),
            ],
            'due_date' => ['nullable', 'date'],
            'status' => ['required', Rule::in(self::SUBTASK_STATUSES)],
        ]);

        $maxOrder = (int) $task->subtasks()->max('sort_order');

        $subtask = Subtask::create([
            'parent_task_id' => $task->id,
            'company_id' => $task->company_id,
            'title' => $validated['title'],
            'assigned_to' => $validated['assigned_to'] ?? null,
            'due_date' => $validated['due_date'] ?? null,
            'status' => $validated['status'],
            'sort_order' => $maxOrder + 1,
            'created_by' => Auth::id(),
        ]);

        $subtask->load('assignee');

        return response()->json([
            'success' => true,
            'subtask' => $this->serializeSubtask($subtask),
        ]);
    }

    public function updateSubtask(Request $request, Subtask $subtask): JsonResponse
    {
        $this->assertSubtaskInCompany($subtask);

        $companyId = (int) Auth::user()->company_id;
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'assigned_to' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id')->where(fn ($q) => $q->where('company_id', $companyId)),
            ],
            'due_date' => ['nullable', 'date'],
            'status' => ['required', Rule::in(self::SUBTASK_STATUSES)],
        ]);

        $subtask->fill([
            'title' => $validated['title'],
            'assigned_to' => $validated['assigned_to'] ?? null,
            'due_date' => $validated['due_date'] ?? null,
            'status' => $validated['status'],
        ]);
        $subtask->save();
        $subtask->load('assignee');

        return response()->json([
            'success' => true,
            'subtask' => $this->serializeSubtask($subtask),
        ]);
    }

    public function deleteSubtask(Subtask $subtask): JsonResponse
    {
        $this->assertSubtaskInCompany($subtask);
        $subtask->delete();

        return response()->json(['success' => true]);
    }

    public function store(Request $request): RedirectResponse
    {
        $companyId = (int) Auth::user()->company_id;

        $request->mergeIfMissing(['status' => 'todo']);

        $validated = $request->validate($this->taskRules($companyId, true));

        $task = Task::create([
            'company_id' => $companyId,
            'title' => $validated['title'],
            'type' => $validated['type'],
            'assigned_to' => $validated['assigned_to'] ?? null,
            'due_date' => $validated['due_date'] ?? null,
            'priority' => $validated['priority'],
            'status' => $validated['status'],
            'post_id' => $validated['post_id'] ?? null,
            'campaign_id' => $validated['campaign_id'] ?? null,
            'created_by' => Auth::id(),
        ]);

        if (! empty($validated['notes'])) {
            TaskNote::create([
                'task_id' => $task->id,
                'user_id' => Auth::id(),
                'note' => $validated['notes'],
            ]);
        }

        return back()->with('success', 'Task created');
    }

    public function update(Request $request, Task $task): JsonResponse|RedirectResponse
    {
        $this->assertTaskInCompany($task);

        $companyId = (int) Auth::user()->company_id;
        $validated = $request->validate($this->taskRules($companyId, false));

        $task->fill([
            'title' => $validated['title'],
            'type' => $validated['type'],
            'assigned_to' => $validated['assigned_to'] ?? null,
            'due_date' => $validated['due_date'] ?? null,
            'priority' => $validated['priority'],
            'status' => $validated['status'],
            'post_id' => $validated['post_id'] ?? null,
            'campaign_id' => $validated['campaign_id'] ?? null,
        ]);
        $task->save();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'task' => $task->fresh()->loadCount(['taskNotes', 'checklists', 'subtasks']),
            ]);
        }

        return back()->with('success', 'Task updated');
    }

    public function destroy(Task $task): RedirectResponse
    {
        $this->assertTaskInCompany($task);
        $task->delete();

        return back()->with('success', 'Task deleted');
    }

    public function updateStatus(Request $request, Task $task): JsonResponse
    {
        $this->assertTaskInCompany($task);

        $validated = $request->validate([
            'status' => ['required', Rule::in(self::STATUSES)],
        ]);

        $task->status = $validated['status'];
        $task->save();

        return response()->json([
            'success' => true,
            'status' => $task->status,
        ]);
    }

    public function addNote(Request $request, Task $task): JsonResponse|RedirectResponse
    {
        $this->assertTaskInCompany($task);

        $validated = $request->validate([
            'note' => ['required', 'string', 'max:2000'],
        ]);

        $note = TaskNote::create([
            'task_id' => $task->id,
            'user_id' => Auth::id(),
            'note' => $validated['note'],
        ]);

        $note->load('user');

        if ($request->expectsJson()) {
            $user = $note->user;

            return response()->json([
                'success' => true,
                'note' => [
                    'id' => $note->id,
                    'note' => $note->note,
                    'user_name' => $user?->name ?: ($user?->email ?? 'User'),
                    'created_at' => $note->created_at?->toIso8601String(),
                ],
            ]);
        }

        return back()->with('success', 'Note added');
    }

    /**
     * @return array<string, mixed>
     */
    private function taskSummaryForJson(Task $t): array
    {
        $post = $t->post;
        $channel = $post?->channel;

        return [
            'id' => $t->id,
            'title' => $t->title,
            'type' => $t->type,
            'priority' => $t->priority,
            'status' => $t->status,
            'due_date' => $t->due_date?->format('Y-m-d'),
            'assigned_to' => $t->assigned_to,
            'assignee_name' => $t->assignee?->name ?? $t->assignee?->email,
            'post_id' => $t->post_id,
            'campaign_id' => $t->campaign_id,
            'task_notes_count' => $t->task_notes_count,
            'checklists_count' => $t->checklists_count ?? 0,
            'completed_checklists_count' => $t->completed_checklists_count ?? 0,
            'subtasks_count' => $t->subtasks_count ?? 0,
            'post_platform' => $channel?->platform,
            'post_title' => $post?->title,
            'campaign_name' => $t->campaign?->name,
            'notes' => $t->taskNotes->map(fn (TaskNote $n) => [
                'note' => $n->note,
                'user_name' => $n->user?->name ?? $n->user?->email,
                'created_at' => $n->created_at?->toIso8601String(),
            ])->values()->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function taskDetailPayload(Task $task): array
    {
        $totalCheck = $task->checklists->count();
        $doneCheck = $task->checklists->where('is_completed', true)->count();
        $checkProgress = $totalCheck > 0 ? (int) round($doneCheck / $totalCheck * 100) : 0;

        $post = $task->post;
        $channel = $post?->channel;
        $platformOptions = ChannelController::platformOptions();
        $platKey = $channel?->platform ?? 'custom';
        $platLabel = $platformOptions[$platKey] ?? '';
        $platformEmoji = (is_string($platLabel) && preg_match('/^(\X)/u', $platLabel, $m)) ? $m[1] : '📄';

        return [
            'id' => $task->id,
            'title' => $task->title,
            'type' => $task->type,
            'priority' => $task->priority,
            'status' => $task->status,
            'due_date' => $task->due_date?->format('Y-m-d'),
            'assigned_to' => $task->assigned_to,
            'assignee_name' => $task->assignee?->name ?? $task->assignee?->email,
            'post_id' => $task->post_id,
            'campaign_id' => $task->campaign_id,
            'checklist_progress' => $checkProgress,
            'checklists_total' => $totalCheck,
            'checklists_completed' => $doneCheck,
            'post' => $post ? [
                'id' => $post->id,
                'title' => $post->title ?: (mb_substr((string) ($post->caption_en ?? ''), 0, 80) ?: 'Untitled'),
                'status' => $post->status,
                'platform' => $channel?->platform ?? 'custom',
                'platform_emoji' => $platformEmoji,
                'edit_url' => route('content.edit', $post),
            ] : null,
            'campaign' => $task->campaign ? [
                'id' => $task->campaign->id,
                'name' => $task->campaign->name,
                'status' => $task->campaign->status,
            ] : null,
            'checklists' => $task->checklists->map(fn (TaskChecklist $c) => $this->serializeChecklist($c))->values()->all(),
            'subtasks' => $task->subtasks->map(fn (Subtask $s) => $this->serializeSubtask($s))->values()->all(),
            'notes' => $task->taskNotes->map(fn (TaskNote $n) => [
                'id' => $n->id,
                'note' => $n->note,
                'user_name' => $n->user?->name ?? $n->user?->email,
                'created_at' => $n->created_at?->toIso8601String(),
            ])->values()->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeChecklist(TaskChecklist $c): array
    {
        $by = $c->completedBy;

        return [
            'id' => $c->id,
            'title' => $c->title,
            'is_completed' => $c->is_completed,
            'sort_order' => $c->sort_order,
            'completed_by_name' => $by?->name ?? $by?->email,
            'completed_at' => $c->completed_at?->toIso8601String(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeSubtask(Subtask $s): array
    {
        $a = $s->assignee;

        return [
            'id' => $s->id,
            'title' => $s->title,
            'status' => $s->status,
            'due_date' => $s->due_date?->format('Y-m-d'),
            'assigned_to' => $s->assigned_to,
            'assignee_name' => $a?->name ?? $a?->email,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function taskRules(int $companyId, bool $withOptionalNotes = false): array
    {
        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(self::TYPES)],
            'assigned_to' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id')->where(fn ($q) => $q->where('company_id', $companyId)),
            ],
            'due_date' => ['nullable', 'date'],
            'priority' => ['required', Rule::in(self::PRIORITIES)],
            'status' => ['required', Rule::in(self::STATUSES)],
            'post_id' => [
                'nullable',
                'integer',
                Rule::exists('posts', 'id')->where(fn ($q) => $q->where('company_id', $companyId)),
            ],
            'campaign_id' => [
                'nullable',
                'integer',
                Rule::exists('campaigns', 'id')->where(fn ($q) => $q->where('company_id', $companyId)),
            ],
        ];

        if ($withOptionalNotes) {
            $rules['notes'] = ['nullable', 'string', 'max:2000'];
        }

        return $rules;
    }

    private function assertTaskInCompany(Task $task): void
    {
        if ($task->company_id !== Auth::user()->company_id) {
            abort(403);
        }
    }

    private function assertChecklistInCompany(TaskChecklist $checklist): void
    {
        $checklist->loadMissing('task');
        if (! $checklist->task || $checklist->task->company_id !== Auth::user()->company_id) {
            abort(403);
        }
    }

    private function assertSubtaskInCompany(Subtask $subtask): void
    {
        $subtask->loadMissing('parentTask');
        if (! $subtask->parentTask || $subtask->parentTask->company_id !== Auth::user()->company_id) {
            abort(403);
        }
    }
}
