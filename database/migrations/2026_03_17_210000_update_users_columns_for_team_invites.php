<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // MySQL-specific column alterations (project uses DB_CONNECTION=mysql)
        DB::statement('ALTER TABLE `users` MODIFY `name` VARCHAR(255) NULL');
        DB::statement('ALTER TABLE `users` MODIFY `password` VARCHAR(255) NULL');
        DB::statement('ALTER TABLE `users` MODIFY `otp_code` VARCHAR(64) NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE `users` MODIFY `otp_code` VARCHAR(10) NULL');
        DB::statement('ALTER TABLE `users` MODIFY `password` VARCHAR(255) NOT NULL');
        DB::statement('ALTER TABLE `users` MODIFY `name` VARCHAR(255) NOT NULL');
    }
};

