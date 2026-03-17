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
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('channel_id')->constrained('channels')->cascadeOnDelete();
            $table->unsignedBigInteger('campaign_id')->nullable();
            $table->string('title', 255)->nullable();
            $table->text('caption_en')->nullable();
            $table->text('caption_ar')->nullable();
            $table->string('media_url', 500)->nullable();
            $table->string('platform_post_id', 255)->nullable();
            $table->string('content_pillar', 100)->nullable();
            $table->enum('status', ['draft', 'in_review', 'approved', 'scheduled', 'published', 'rejected'])
                ->default('draft');
            $table->text('brief')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
