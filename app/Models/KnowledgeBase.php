<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KnowledgeBase extends Model
{
    use HasFactory;

    protected $table = 'knowledge_base';

    protected $fillable = [
        'category',
        'industry',
        'platform',
        'region',
        'content_text',
        'priority',
    ];
}

