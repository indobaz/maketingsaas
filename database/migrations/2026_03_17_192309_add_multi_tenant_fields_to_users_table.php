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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('company_id')
                ->nullable()
                ->constrained('companies')
                ->nullOnDelete();

            $table->string('phone', 20)->nullable();
            $table->enum('role', ['owner', 'admin', 'editor', 'viewer'])->default('editor');
            $table->enum('status', ['active', 'invited', 'inactive'])->default('invited');

            $table->string('otp_code', 10)->nullable();
            $table->timestamp('otp_expires_at')->nullable();

            $table->unsignedBigInteger('invited_by')->nullable();
            $table->foreign('invited_by')->references('id')->on('users')->nullOnDelete();

            $table->timestamp('last_login_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropColumn('company_id');

            $table->dropForeign(['invited_by']);
            $table->dropColumn('invited_by');

            $table->dropColumn([
                'phone',
                'role',
                'status',
                'otp_code',
                'otp_expires_at',
                'last_login_at',
            ]);
        });
    }
};
