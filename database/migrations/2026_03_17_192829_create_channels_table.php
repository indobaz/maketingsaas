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
        Schema::create('channels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('name', 100);
            $table->enum('platform', [
                'instagram',
                'youtube',
                'linkedin',
                'tiktok',
                'facebook',
                'twitter',
                'pinterest',
                'snapchat',
                'whatsapp',
                'custom',
            ]);
            $table->string('handle', 100)->nullable();
            $table->string('color', 7)->default('#5F63F2');
            $table->boolean('api_connected')->default(false);
            $table->text('api_token')->nullable();
            $table->timestamp('api_token_expires_at')->nullable();
            $table->integer('followers_count')->default(0);
            $table->enum('status', ['active', 'archived'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('channels');
    }
};
