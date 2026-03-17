<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Campaign extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'name',
        'goal',
        'start_date',
        'end_date',
        'budget',
        'status',
        'ai_generated',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'budget' => 'decimal:2',
        'ai_generated' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }
}

