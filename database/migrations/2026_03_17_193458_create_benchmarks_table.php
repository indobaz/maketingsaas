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
        Schema::create('benchmarks', function (Blueprint $table) {
            $table->id();
            $table->string('industry', 100);
            $table->string('platform', 100);
            $table->string('metric', 100);
            $table->decimal('avg_value', 10, 4);
            $table->integer('sample_size')->default(0);
            $table->date('period_week');
            $table->timestamp('computed_at')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('benchmarks');
    }
};
