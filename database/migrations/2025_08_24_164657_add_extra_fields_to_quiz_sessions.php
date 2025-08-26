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
        Schema::table('quiz_sessions', function (Blueprint $table) {
            $table->enum('access_type', ['public_code', 'restricted_list'])
                  ->default('public_code')
                  ->after('status');

            $table->integer('duration_override')
                  ->nullable()
                  ->after('require_student_list');

            $table->integer('attempts_allowed')
                  ->default(1)
                  ->after('duration_override');

            // Optionnel : si tu veux relier une classe
            // $table->foreignId('class_id')->nullable()
            //       ->constrained()
            //       ->nullOnDelete()
            //       ->after('teacher_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quiz_sessions', function (Blueprint $table) {
            $table->dropColumn(['access_type', 'duration_override', 'attempts_allowed']);
            // $table->dropConstrainedForeignId('class_id'); // si tu l'ajoutes
        });
    }
};
