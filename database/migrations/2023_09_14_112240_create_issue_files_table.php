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
        Schema::create('issue_files', function (Blueprint $table) {
            $table->id();
            $table->string('files');
            $table->unsignedBigInteger('issue_id');
            $table->foreign('issue_id')->references('id')->on('issues');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('issue_files');
    }
};
