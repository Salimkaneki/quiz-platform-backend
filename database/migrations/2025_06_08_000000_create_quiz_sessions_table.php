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
        Schema::create('quiz_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained()->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->string('session_code', 8)->unique(); // Code d'accès (ex: ABC123XY)
            $table->string('title')->nullable(); // Titre de la session
            $table->datetime('starts_at');
            $table->datetime('ends_at');
            $table->enum('status', ['scheduled', 'active', 'paused', 'completed', 'cancelled'])
                  ->default('scheduled');
            $table->json('allowed_students')->nullable(); // IDs des étudiants autorisés
            $table->integer('max_participants')->nullable();
            $table->boolean('require_student_list')->default(true);
            $table->json('settings')->nullable(); // Paramètres spécifiques à la session
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            
            $table->index(['session_code']);
            $table->index(['teacher_id', 'status']);
            $table->index(['starts_at', 'ends_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quiz_sessions');
    }
};
