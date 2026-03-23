<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailTemplate extends Model
{
    protected $fillable = [
        'company_id',
        'template_key',
        'name',
        'subject',
        'body_html',
        'variables',
        'is_active',
    ];

    protected $casts = [
        'variables' => 'array',
        'is_active' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public static function getForCompany(mixed $companyId, string $templateKey): ?self
    {
        if ($companyId !== null) {
            $companyTemplate = static::query()
                ->where('company_id', $companyId)
                ->where('template_key', $templateKey)
                ->where('is_active', true)
                ->first();

            if ($companyTemplate) {
                return $companyTemplate;
            }
        }

        return static::query()
            ->whereNull('company_id')
            ->where('template_key', $templateKey)
            ->where('is_active', true)
            ->first();
    }
}
