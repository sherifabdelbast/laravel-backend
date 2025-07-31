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
        Schema::create('issues', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->longText('description')->nullable();
            $table->json('estimated_at')->nullable();
            $table->boolean('deleted')->nullable();
            $table->integer('order')->nullable();
            $table->integer('order_by_status')->nullable();
            $table->unsignedBigInteger('assign_to')->nullable();
            $table->foreign('assign_to')->references('id')->on('teams');
            $table->unsignedBigInteger('sprint_id')->nullable();
            $table->foreign('sprint_id')->references('id')->on('sprints');
            $table->unsignedBigInteger('status_id');
            $table->foreign('status_id')->references('id')->on('statuses');
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
        Schema::dropIfExists('issues');
    }
};
