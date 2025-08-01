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
        Schema::create('request_forget_passwords', function (Blueprint $table) {
            $table->id();
            $table->integer('code');
            $table->string('identify_number');
            $table->timestamp('expired_at');
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('request_forget_passwords');
    }
};
