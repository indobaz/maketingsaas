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
        'post_type',
        'carousel_urls',
        'hook_line',
        'video_instructions',
        'thumbnail_url',
        'duration',
        'link_sticker_url',
        'primary_goal',
        'target_audience',
        'utm_url',
        'script',
        'on_screen_text',
        'trending_audio_url',
        'slide_urls',
        'slide_headlines',
        'slide_copy',
        'cta_slide',
        'alt_texts',
        'pdf_url',
        'video_url',
        'thumbnail_b_url',
        'video_title',
        'seo_description',
        'chapters',
        'end_screen_plan',
        'geo_tag',
        'product_tags',
        'story_asset_urls',
        'sticker_type',
        'sticker_text',
        'sticker_link_url',
        'story_sequence',
        'thread_posts',
        'poll_question',
        'poll_options',
        'original_post_url',
        'ugc_creator_handle',
        'ugc_permission',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'published_at' => 'datetime',
        'carousel_urls' => 'array',
        'slide_urls' => 'array',
        'slide_headlines' => 'array',
        'slide_copy' => 'array',
        'alt_texts' => 'array',
        'story_asset_urls' => 'array',
        'thread_posts' => 'array',
        'poll_options' => 'array',
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

    public function comments(): HasMany
    {
        return $this->hasMany(PostComment::class)->latest();
    }
}
