<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'title',
        'type',
        'assigned_to',
        'due_date',
        'priority',
        'status',
        'post_id',
        'campaign_id',
        'is_recurring',
        'recurrence_rule',
        'created_by',
    ];

    protected $casts = [
        'due_date' => 'date',
        'is_recurring' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function taskNotes(): HasMany
    {
        return $this->hasMany(TaskNote::class);
    }

    public function checklists(): HasMany
    {
        return $this->hasMany(TaskChecklist::class)->orderBy('sort_order');
    }

    public function subtasks(): HasMany
    {
        return $this->hasMany(Subtask::class, 'parent_task_id')->orderBy('sort_order');
    }
}
