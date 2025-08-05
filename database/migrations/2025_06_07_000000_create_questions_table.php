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
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained()->cascadeOnDelete();
            $table->text('question_text');
            $table->enum('type', ['multiple_choice', 'true_false', 'open_ended', 'fill_blank']);
            $table->json('options')->nullable(); // Pour QCM: [{text: "Option A", is_correct: true}]
            $table->text('correct_answer')->nullable(); // Pour questions ouvertes
            $table->integer('points')->default(1);
            $table->integer('order')->default(1); // Ordre d'affichage
            $table->text('explanation')->nullable(); // Explication de la réponse
            $table->string('image_url')->nullable(); // Image de la question
            $table->integer('time_limit')->nullable(); // Temps limite par question (secondes)
            $table->json('metadata')->nullable(); // Données supplémentaires
            $table->timestamps();
            
            $table->index(['quiz_id', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
