<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Benchmark extends Model
{
    use HasFactory;

    protected $table = 'benchmarks';

    protected $fillable = [
        'industry',
        'platform',
        'metric',
        'avg_value',
        'sample_size',
        'period_week',
        'computed_at',
    ];

    protected $casts = [
        'period_week' => 'date',
        'computed_at' => 'datetime',
    ];
}

