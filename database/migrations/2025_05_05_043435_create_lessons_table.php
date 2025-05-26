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
        Schema::create('lessons', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('title')->nullable();
            $table->text('description');
            $table->string('video_link')->nullable();
            $table->integer('duration')->nullable();
            $table->integer('order_number')->nullable();
            $table->foreignUlid('course_id')->references('id')->on('courses')->onDelete('cascade');
            $table->timestamps();
            $table->unique(['course_id', 'order_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lessons');
    }
};
