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
        Schema::table('notifications',function (Blueprint $table){
            Schema::disableForeignKeyConstraints();
            $table->unsignedBigInteger('invitation_id')->nullable();
            $table->foreign('invitation_id')->references('id')->on('invitations');
            Schema::enableForeignKeyConstraints();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
