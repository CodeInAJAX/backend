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
        Schema::create('ratings', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->integer('rating')->unsigned()->nullable();
            $table->text('comment');
            $table->foreignUlid('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreignUlid('course_id')->references('id')->on('courses')->onDelete('cascade');
            $table->timestamps();
            $table->unique(['user_id', 'course_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ratings');
    }
};
