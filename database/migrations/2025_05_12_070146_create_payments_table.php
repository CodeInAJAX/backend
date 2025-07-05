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
        Schema::create('payments', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->enum('status', ['pending', 'success', 'failed'])->default('pending');
            $table->integer('amount')->nullable(false);
            $table->string('currency')->nullable(false);
            $table->enum('payment_method', ['credit_card', 'bank_transfer', 'e_wallet', 'cash'])->default('cash');
            $table->foreignUlid('user_id')->references('id')->on('users');
            $table->foreignUlid('course_id')->references('id')->on('courses');
            $table->unique(['course_id', 'user_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
