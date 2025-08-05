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
        Schema::create('classes', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Ex: "L3-INFO-A"
            $table->integer('level'); // 1, 2, 3 pour L1, L2, L3
            $table->string('academic_year'); // Ex: "2024-2025"
            $table->foreignId('formation_id')->constrained('formations') // Correction: constrained au lieu de contrained
                ->onDelete('cascade');
            $table->integer('max_students')->default(50); 
            $table->boolean('is_active')->default(true); // Correction: boolean au lieu de integer
            $table->timestamps();

            $table->index(['formation_id', 'academic_year']);
            $table->unique(['formation_id', 'name', 'academic_year']); // Correction: formation_id au lieu de foramtion_id
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('classes');
    }
};