<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Pour SQLite, mapper simplement les statuts existants
        // SQLite ne supporte pas les ALTER TYPE comme PostgreSQL
        DB::statement("UPDATE quiz_sessions SET status = 'active' WHERE status = 'paused'");
        DB::statement("UPDATE quiz_sessions SET status = 'completed' WHERE status = 'cancelled'");
        
        // Note: La contrainte enum sera gérée au niveau application
        // Les nouveaux statuts acceptés sont: 'scheduled', 'active', 'completed'
    }

    public function down(): void
    {
        // Remettre les anciens statuts (approximation)
        // Note: Cette restauration n'est pas parfaite car on ne peut pas
        // distinguer les anciens 'paused' des nouveaux 'active'
        DB::statement("UPDATE quiz_sessions SET status = 'cancelled' WHERE status = 'completed' AND completed_at IS NULL");
    }
};
