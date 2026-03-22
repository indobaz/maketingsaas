<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContentPillar extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'name',
        'color',
        'target_percentage',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Posts store pillar as a string column matching this pillar's name (same company).
     */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class, 'content_pillar', 'name')
            ->whereColumn('posts.company_id', 'content_pillars.company_id');
    }
}
