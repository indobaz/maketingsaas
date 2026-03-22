<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->string('post_type', 20)->nullable()->default('static')->after('content_pillar');
            $table->json('carousel_urls')->nullable()->after('post_type');
            $table->string('hook_line', 150)->nullable()->after('carousel_urls');
            $table->text('video_instructions')->nullable()->after('hook_line');
            $table->string('thumbnail_url', 500)->nullable()->after('video_instructions');
            $table->string('duration', 20)->nullable()->after('thumbnail_url');
            $table->string('link_sticker_url', 500)->nullable()->after('duration');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn([
                'post_type',
                'carousel_urls',
                'hook_line',
                'video_instructions',
                'thumbnail_url',
                'duration',
                'link_sticker_url',
            ]);
        });
    }
};
