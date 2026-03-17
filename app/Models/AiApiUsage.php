<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiApiUsage extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'provider',
        'model',
        'task_type',
        'tokens_used',
        'cost_usd',
    ];

    protected $casts = [
        'cost_usd' => 'decimal:6',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}

