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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('title', 255);
            $table->enum('type', ['content', 'shooting', 'editing', 'design', 'publishing', 'campaign', 'general'])
                ->default('general');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->date('due_date')->nullable();
            $table->enum('priority', ['high', 'medium', 'low'])->default('medium');
            $table->enum('status', ['todo', 'in_progress', 'in_review', 'done'])->default('todo');
            $table->unsignedBigInteger('post_id')->nullable();
            $table->unsignedBigInteger('campaign_id')->nullable();
            $table->boolean('is_recurring')->default(false);
            $table->string('recurrence_rule', 100)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
