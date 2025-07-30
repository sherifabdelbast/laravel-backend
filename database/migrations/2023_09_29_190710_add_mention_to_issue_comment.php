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
        Schema::table('issues', function (Blueprint $table) {
            $table->json('mention_list')->nullable();
        });

        Schema::table('comments', function (Blueprint $table) {
            $table->json('mention_list')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('issue_comment', function (Blueprint $table) {
            $table->dropColumn('mention_list');
        });

        Schema::table('comments', function (Blueprint $table) {
            $table->dropColumn('mention_list');
        });
    }
};
