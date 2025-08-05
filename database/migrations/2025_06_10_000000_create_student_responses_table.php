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
        Schema::create('student_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            $table->json('answer'); // Réponse de l'étudiant (flexible selon le type)
            $table->boolean('is_correct')->nullable(); // NULL = pas encore corrigé
            $table->decimal('points_earned', 5, 2)->default(0);
            $table->decimal('points_possible', 5, 2);
            $table->integer('time_spent')->nullable(); // Temps passé en secondes
            $table->timestamp('answered_at');
            $table->timestamp('reviewed_at')->nullable(); // Quand corrigé manuellement
            $table->foreignId('reviewed_by')->nullable()->constrained('users');
            $table->text('teacher_comment')->nullable();
            $table->timestamps();
            
            $table->unique(['quiz_session_id', 'student_id', 'question_id']);
            $table->index(['student_id', 'quiz_session_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_responses');
    }
};
