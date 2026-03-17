<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'module',
        'platform',
        'filename',
        'rows_imported',
        'field_mapping_json',
        'imported_by',
    ];

    protected $casts = [
        'field_mapping_json' => 'array',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function importer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'imported_by');
    }
}

