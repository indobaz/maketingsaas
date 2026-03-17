<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Channel extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'name',
        'platform',
        'handle',
        'color',
        'api_connected',
        'api_token',
        'api_token_expires_at',
        'followers_count',
        'status',
        'notes',
    ];

    protected $casts = [
        'api_connected' => 'boolean',
        'api_token_expires_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function followerSnapshots(): HasMany
    {
        return $this->hasMany(FollowerSnapshot::class);
    }
}

