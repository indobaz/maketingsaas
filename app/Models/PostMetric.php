<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostMetric extends Model
{
    use HasFactory;

    protected $fillable = [
        'post_id',
        'company_id',
        'likes',
        'comments_count',
        'shares',
        'reach',
        'impressions',
        'saves',
        'views',
        'engagement_rate',
        'recorded_at',
    ];

    protected $casts = [
        'engagement_rate' => 'decimal:2',
        'recorded_at' => 'datetime',
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}

