<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Step 1: Remove AUTO_INCREMENT using raw SQL
        DB::statement("ALTER TABLE `notifications` MODIFY `id` BIGINT(20) UNSIGNED");

        // Step 2: Drop primary key
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropPrimary();
        });

        // Step 3: Change column type to CHAR(36)
        Schema::table('notifications', function (Blueprint $table) {
            $table->char('id', 36)->change();
        });

        // Step 4: Set primary key again
        Schema::table('notifications', function (Blueprint $table) {
            $table->primary('id');
        });
    }

    public function down()
    {
        // Revert back to bigint auto increment
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropPrimary();
        });

        DB::statement("ALTER TABLE `notifications` MODIFY `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT");

        Schema::table('notifications', function (Blueprint $table) {
            $table->primary('id');
        });
    }
};