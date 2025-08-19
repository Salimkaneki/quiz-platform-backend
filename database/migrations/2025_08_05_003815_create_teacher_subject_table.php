<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_subject', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('teachers')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete(); // Spécifier 'subjects'
            $table->foreignId('classe_id')->nullable()->constrained('classes')->cascadeOnDelete(); // Spécifier 'classes'
            $table->string('academic_year');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->unique(['teacher_id', 'subject_id', 'classe_id', 'academic_year'], 'teacher_subject_unique');
            $table->index(['teacher_id', 'academic_year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_subject');
    }
};