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
        Schema::create('results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->decimal('total_points', 8, 2);
            $table->decimal('max_points', 8, 2);
            $table->decimal('percentage', 5, 2); // Pourcentage
            $table->decimal('grade', 5, 2)->nullable(); // Note finale (sur 20 par ex)
            $table->enum('status', ['in_progress', 'submitted', 'graded', 'published'])->default('in_progress');
            $table->integer('total_questions');
            $table->integer('correct_answers');
            $table->integer('time_spent_total')->nullable(); // Temps total en secondes
            $table->timestamp('started_at');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('graded_at')->nullable();
            $table->timestamp('published_at')->nullable(); // Quand visible par l'étudiant
            $table->json('detailed_stats')->nullable(); // Stats détaillées
            $table->text('teacher_feedback')->nullable();
            $table->timestamps();
            
            $table->unique(['quiz_session_id', 'student_id']);
            $table->index(['student_id', 'status']);
            $table->index(['quiz_session_id', 'percentage']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('results');
    }
};
