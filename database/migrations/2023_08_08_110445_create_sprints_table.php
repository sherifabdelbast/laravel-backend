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
        Schema::create('sprints', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('goal')->nullable();
            $table->timestamp('start_date')->nullable();
            $table->enum('duration',['1 Week', '2 Week', '3 Week', '4 Week'])->default('1 Week');
            $table->timestamp('end_date')->nullable();
            $table->enum('status', ['IDLE', 'IN PROGRESS', 'COMPLETED', 'FAILED'])->default('IDLE');
            $table->unsignedBigInteger('project_id');
            $table->foreign('project_id')->references('id')->on('projects');
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sprints');
    }
};
