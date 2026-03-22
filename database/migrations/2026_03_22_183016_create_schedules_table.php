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
        Schema::create('schedules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('school_id')->constrained('schools')->onDelete('cascade');
            $table->foreignUuid('class_id')->constrained('school_classes')->onDelete('cascade');
            $table->string('subject');
            $table->enum('day_of_week', ['LUNDI', 'MARDI', 'MERCREDI', 'JEUDI', 'VENDREDI', 'SAMEDI', 'DIMANCHE']);
            $table->time('start_time');
            $table->time('end_time');
            $table->string('room')->nullable();
            $table->string('teacher')->nullable();
            $table->enum('type', ['COURSE', 'TD', 'TP', 'EXAMEN']);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
