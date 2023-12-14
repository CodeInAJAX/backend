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
        Schema::create('enrollments', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->enum('status', ['active', 'completed'])->default('active');
            $table->foreignUlid('course_id')->references('id')->on('courses')->onDelete('cascade');
            $table->foreignUlid('student_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['course_id', 'student_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enrollments');
    }
};
