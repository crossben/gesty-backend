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
        Schema::table('academic_items', function (Blueprint $table) {
            if (!Schema::hasColumn('academic_items', 'difficulty')) {
                $table->enum('difficulty', ['EASY', 'MEDIUM', 'HARD'])->default('MEDIUM');
            }
            if (!Schema::hasColumn('academic_items', 'status')) {
                $table->enum('status', ['PENDING', 'IN_PROGRESS', 'COMPLETED', 'CANCELLED'])->default('PENDING');
            }
            if (!Schema::hasColumn('academic_items', 'is_ai_generated')) {
                $table->boolean('is_ai_generated')->default(false);
            }
            if (!Schema::hasColumn('academic_items', 'ai_content')) {
                $table->json('ai_content')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('academic_items', function (Blueprint $table) {
            $columns = ['difficulty', 'status', 'is_ai_generated', 'ai_content'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('academic_items', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
