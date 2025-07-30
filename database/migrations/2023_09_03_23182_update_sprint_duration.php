<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {

        DB::statement("ALTER TABLE sprints MODIFY COLUMN duration
        ENUM('1 Week', '2 Weeks', '3 Weeks', '4 Weeks') DEFAULT '1 Week'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
