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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('slug', 100)->unique();
            $table->string('logo_url', 500)->nullable();
            $table->string('industry', 100)->nullable();
            $table->string('country', 100)->nullable();
            $table->string('timezone', 100)->default('Asia/Dubai');
            $table->string('website', 500)->nullable();
            $table->string('primary_color', 7)->default('#5F63F2');
            $table->string('secondary_color', 7)->default('#272B41');
            $table->enum('plan', ['free', 'starter', 'pro', 'enterprise'])->default('free');
            $table->timestamp('plan_expires_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
