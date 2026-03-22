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
        Schema::create('announcements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('school_id')->constrained()->onDelete('cascade');
            $table->foreignUuid('author_id')->constrained('users')->onDelete('cascade');
            $table->foreignUuid('class_id')->nullable()->constrained('school_classes')->onDelete('cascade');
            $table->string('title');
            $table->text('content');
            $table->enum('priority', ['LOW', 'NORMAL', 'HIGH', 'URGENT'])->default('NORMAL');
            $table->dateTime('expires_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
