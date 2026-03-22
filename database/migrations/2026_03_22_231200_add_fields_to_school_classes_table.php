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
        Schema::table('school_classes', function (Blueprint $table) {
            $table->string('code')->nullable()->after('name');
            $table->unsignedInteger('capacity')->nullable()->after('code');
            $table->string('academic_year', 20)->nullable()->after('capacity');
            $table->boolean('is_active')->default(true)->after('academic_year');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('school_classes', function (Blueprint $table) {
            $table->dropColumn(['code', 'capacity', 'academic_year', 'is_active']);
        });
    }
};
