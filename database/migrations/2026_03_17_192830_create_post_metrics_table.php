<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('post_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained('posts')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->integer('likes')->default(0);
            $table->integer('comments_count')->default(0);
            $table->integer('shares')->default(0);
            $table->integer('reach')->default(0);
            $table->integer('impressions')->default(0);
            $table->integer('saves')->default(0);
            $table->integer('views')->default(0);
            $table->decimal('engagement_rate', 5, 2)->default(0);
            $table->timestamp('recorded_at')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_metrics');
    }
};
