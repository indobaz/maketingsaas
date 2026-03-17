<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'industry',
        'country',
        'timezone',
        'website',
        'logo_url',
        'primary_color',
        'secondary_color',
        'plan',
        'plan_expires_at',
    ];

    protected $casts = [
        'plan_expires_at' => 'datetime',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function channels(): HasMany
    {
        return $this->hasMany(Channel::class);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function campaigns(): HasMany
    {
        return $this->hasMany(Campaign::class);
    }

    public function contentPillars(): HasMany
    {
        return $this->hasMany(ContentPillar::class);
    }
}

