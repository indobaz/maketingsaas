<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
}

