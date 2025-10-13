<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Ajoute les index manquants pour optimiser les requêtes fréquentes sur institution_id
     */
    public function up(): void
    {
        // Index pour la table students - institution_id fréquemment utilisé
        Schema::table('students', function (Blueprint $table) {
            $table->index(['institution_id', 'is_active'], 'idx_students_institution_active');
            $table->index('institution_id', 'idx_students_institution');
        });

        // Index pour la table teachers - institution_id fréquemment utilisé dans les contrôles d'autorisation
        Schema::table('teachers', function (Blueprint $table) {
            $table->index(['institution_id', 'is_permanent'], 'idx_teachers_institution_permanent');
            $table->index('institution_id', 'idx_teachers_institution');
        });

        // Index pour la table institutions - code et is_active fréquemment utilisés
        Schema::table('institutions', function (Blueprint $table) {
            $table->index(['code', 'is_active'], 'idx_institutions_code_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Supprimer les index dans l'ordre inverse
        Schema::table('institutions', function (Blueprint $table) {
            $table->dropIndex('idx_institutions_code_active');
        });

        Schema::table('teachers', function (Blueprint $table) {
            $table->dropIndex('idx_teachers_institution_permanent');
            $table->dropIndex('idx_teachers_institution');
        });

        Schema::table('students', function (Blueprint $table) {
            $table->dropIndex('idx_students_institution_active');
            $table->dropIndex('idx_students_institution');
        });
    }
};
