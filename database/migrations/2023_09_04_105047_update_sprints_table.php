<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sprints', function (Blueprint $table) {
            $table->boolean('is_completed')->default(0);
            $table->boolean('is_open')->default(0);
            $table->boolean('deleted')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sprints', function (Blueprint $table) {
            $table->dropColumn('is_completed');
            $table->dropColumn('is_open');
            $table->dropColumn('deleted');
        });
    }
};
