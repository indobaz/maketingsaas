<?php

use Closure;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            if (Schema::hasColumn('posts', 'post_type')) {
                DB::statement("ALTER TABLE `posts` MODIFY `post_type` VARCHAR(30) NULL DEFAULT 'static_image'");
            }
            if (Schema::hasColumn('posts', 'hook_line')) {
                DB::statement('ALTER TABLE `posts` MODIFY `hook_line` VARCHAR(200) NULL');
            }
        }

        Schema::table('posts', function (Blueprint $table) {
            if (! Schema::hasColumn('posts', 'post_type')) {
                $table->string('post_type', 30)->nullable()->default('static_image');
            }

            $this->addColumnIfMissing($table, 'hook_line', fn () => $table->string('hook_line', 200)->nullable());
            $this->addColumnIfMissing($table, 'primary_goal', fn () => $table->string('primary_goal', 50)->nullable());
            $this->addColumnIfMissing($table, 'target_audience', fn () => $table->text('target_audience')->nullable());
            $this->addColumnIfMissing($table, 'utm_url', fn () => $table->string('utm_url', 500)->nullable());
            $this->addColumnIfMissing($table, 'script', fn () => $table->text('script')->nullable());
            $this->addColumnIfMissing($table, 'on_screen_text', fn () => $table->text('on_screen_text')->nullable());
            $this->addColumnIfMissing($table, 'trending_audio_url', fn () => $table->string('trending_audio_url', 500)->nullable());
            $this->addColumnIfMissing($table, 'slide_urls', fn () => $table->json('slide_urls')->nullable());
            $this->addColumnIfMissing($table, 'slide_headlines', fn () => $table->json('slide_headlines')->nullable());
            $this->addColumnIfMissing($table, 'slide_copy', fn () => $table->json('slide_copy')->nullable());
            $this->addColumnIfMissing($table, 'cta_slide', fn () => $table->text('cta_slide')->nullable());
            $this->addColumnIfMissing($table, 'alt_texts', fn () => $table->json('alt_texts')->nullable());
            $this->addColumnIfMissing($table, 'pdf_url', fn () => $table->string('pdf_url', 500)->nullable());
            $this->addColumnIfMissing($table, 'video_url', fn () => $table->string('video_url', 500)->nullable());
            $this->addColumnIfMissing($table, 'thumbnail_b_url', fn () => $table->string('thumbnail_b_url', 500)->nullable());
            $this->addColumnIfMissing($table, 'video_title', fn () => $table->string('video_title', 255)->nullable());
            $this->addColumnIfMissing($table, 'seo_description', fn () => $table->text('seo_description')->nullable());
            $this->addColumnIfMissing($table, 'chapters', fn () => $table->text('chapters')->nullable());
            $this->addColumnIfMissing($table, 'end_screen_plan', fn () => $table->text('end_screen_plan')->nullable());
            $this->addColumnIfMissing($table, 'geo_tag', fn () => $table->string('geo_tag', 200)->nullable());
            $this->addColumnIfMissing($table, 'product_tags', fn () => $table->text('product_tags')->nullable());
            $this->addColumnIfMissing($table, 'story_asset_urls', fn () => $table->json('story_asset_urls')->nullable());
            $this->addColumnIfMissing($table, 'sticker_type', fn () => $table->string('sticker_type', 50)->nullable());
            $this->addColumnIfMissing($table, 'sticker_text', fn () => $table->text('sticker_text')->nullable());
            $this->addColumnIfMissing($table, 'sticker_link_url', fn () => $table->string('sticker_link_url', 500)->nullable());
            $this->addColumnIfMissing($table, 'story_sequence', fn () => $table->integer('story_sequence')->nullable());
            $this->addColumnIfMissing($table, 'thread_posts', fn () => $table->json('thread_posts')->nullable());
            $this->addColumnIfMissing($table, 'poll_question', fn () => $table->string('poll_question', 500)->nullable());
            $this->addColumnIfMissing($table, 'poll_options', fn () => $table->json('poll_options')->nullable());
            $this->addColumnIfMissing($table, 'original_post_url', fn () => $table->string('original_post_url', 500)->nullable());
            $this->addColumnIfMissing($table, 'ugc_creator_handle', fn () => $table->string('ugc_creator_handle', 200)->nullable());
            $this->addColumnIfMissing($table, 'ugc_permission', fn () => $table->string('ugc_permission', 50)->nullable());
        });
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            if (Schema::hasColumn('posts', 'post_type')) {
                DB::statement("ALTER TABLE `posts` MODIFY `post_type` VARCHAR(20) NULL DEFAULT 'static'");
            }
            if (Schema::hasColumn('posts', 'hook_line')) {
                DB::statement('ALTER TABLE `posts` MODIFY `hook_line` VARCHAR(150) NULL');
            }
        }

        $drop = array_filter([
            'primary_goal',
            'target_audience',
            'utm_url',
            'script',
            'on_screen_text',
            'trending_audio_url',
            'slide_urls',
            'slide_headlines',
            'slide_copy',
            'cta_slide',
            'alt_texts',
            'pdf_url',
            'video_url',
            'thumbnail_b_url',
            'video_title',
            'seo_description',
            'chapters',
            'end_screen_plan',
            'geo_tag',
            'product_tags',
            'story_asset_urls',
            'sticker_type',
            'sticker_text',
            'sticker_link_url',
            'story_sequence',
            'thread_posts',
            'poll_question',
            'poll_options',
            'original_post_url',
            'ugc_creator_handle',
            'ugc_permission',
        ], fn (string $c) => Schema::hasColumn('posts', $c));

        if ($drop !== []) {
            Schema::table('posts', function (Blueprint $table) use ($drop) {
                $table->dropColumn($drop);
            });
        }
    }

    private function addColumnIfMissing(Blueprint $table, string $column, Closure $callback): void
    {
        if (! Schema::hasColumn('posts', $column)) {
            $callback();
        }
    }
};
