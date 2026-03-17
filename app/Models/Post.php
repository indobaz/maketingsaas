<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'channel_id',
        'campaign_id',
        'title',
        'caption_en',
        'caption_ar',
        'media_url',
        'platform_post_id',
        'content_pillar',
        'status',
        'brief',
        'scheduled_at',
        'published_at',
        'created_by',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'published_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function channel(): BelongsTo
    {
        return $this->belongsTo(Channel::class);
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function postMetrics(): HasMany
    {
        return $this->hasMany(PostMetric::class);
    }
}

