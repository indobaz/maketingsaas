<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FollowerSnapshot extends Model
{
    use HasFactory;

    protected $fillable = [
        'channel_id',
        'company_id',
        'follower_count',
        'recorded_date',
    ];

    protected $casts = [
        'recorded_date' => 'date',
    ];

    public function channel(): BelongsTo
    {
        return $this->belongsTo(Channel::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}

