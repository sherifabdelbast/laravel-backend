<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('project_histories', function (Blueprint $table) {
            DB::forgetRecordModificationState();
            $table->unsignedBigInteger('status_received_action')->nullable();
            $table->foreign('status_received_action')
                ->references('id')->on('statuses');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_histories', function (Blueprint $table) {
            $table->dropForeign(['status_received_action']);
            $table->dropColumn('status_received_action');
        });
    }
};
