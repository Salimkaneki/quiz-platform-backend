<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Ajoute des index de performance pour optimiser les requêtes fréquentes
     */
    public function up(): void
    {
        // Index pour la table results - très fréquemment utilisé dans les contrôleurs
        Schema::table('results', function (Blueprint $table) {
            $table->index(['quiz_session_id', 'student_id'], 'idx_results_session_student');
        });

        // Index pour la table student_responses - utilisé dans presque toutes les requêtes de réponses
        Schema::table('student_responses', function (Blueprint $table) {
            $table->index(['quiz_session_id', 'student_id', 'question_id'], 'idx_responses_session_student_question');
        });

        // Index pour la table users - fréquemment utilisés pour filtrer par type/rôle
        Schema::table('users', function (Blueprint $table) {
            $table->index(['account_type', 'is_active'], 'idx_users_type_active');
            $table->index('account_type', 'idx_users_type');
        });

        // Index pour la table platform_notifications - pour optimiser les requêtes par utilisateur et type
        Schema::table('platform_notifications', function (Blueprint $table) {
            $table->index(['user_id', 'type'], 'idx_notifications_user_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Supprimer les index dans l'ordre inverse
        Schema::table('platform_notifications', function (Blueprint $table) {
            $table->dropIndex('idx_notifications_user_type');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_type_active');
            $table->dropIndex('idx_users_type');
        });

        Schema::table('student_responses', function (Blueprint $table) {
            $table->dropIndex('idx_responses_session_student_question');
        });

        Schema::table('results', function (Blueprint $table) {
            $table->dropIndex('idx_results_session_student');
        });
    }
};
