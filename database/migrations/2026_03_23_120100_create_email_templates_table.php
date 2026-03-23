<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('template_key', 100);
            $table->string('name', 200);
            $table->string('subject', 500);
            $table->longText('body_html');
            $table->json('variables');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['company_id', 'template_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_templates');
    }
};
