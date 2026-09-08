<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * `quizzes.teacher_id` et `quiz_sessions.teacher_id` référençaient `users.id`
 * alors que le code et les seeders y écrivaient tantôt `users.id`, tantôt
 * `teachers.id`. On aligne tout sur `teachers.id`, cohérent avec
 * `teacher_subject.teacher_id`.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Remappe les lignes qui contiennent encore un users.id vers le teachers.id
        // correspondant. Les lignes déjà correctes ne bougent pas.
        foreach (['quizzes', 'quiz_sessions'] as $table) {
            DB::statement("
                UPDATE {$table} t
                JOIN teachers te ON te.user_id = t.teacher_id
                LEFT JOIN teachers ok ON ok.id = t.teacher_id
                SET t.teacher_id = te.id
                WHERE ok.id IS NULL
            ");
        }

        // Les lignes orphelines (ni users.id ni teachers.id valide) bloqueraient
        // la création de la contrainte : on les supprime.
        foreach (['quizzes', 'quiz_sessions'] as $table) {
            DB::statement("
                DELETE t FROM {$table} t
                LEFT JOIN teachers te ON te.id = t.teacher_id
                WHERE te.id IS NULL
            ");
        }

        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropForeign(['teacher_id']);
            $table->foreign('teacher_id')->references('id')->on('teachers')->cascadeOnDelete();
        });

        Schema::table('quiz_sessions', function (Blueprint $table) {
            $table->dropForeign(['teacher_id']);
            $table->foreign('teacher_id')->references('id')->on('teachers')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        foreach (['quizzes', 'quiz_sessions'] as $table) {
            DB::statement("
                UPDATE {$table} t
                JOIN teachers te ON te.id = t.teacher_id
                SET t.teacher_id = te.user_id
            ");
        }

        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropForeign(['teacher_id']);
            $table->foreign('teacher_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::table('quiz_sessions', function (Blueprint $table) {
            $table->dropForeign(['teacher_id']);
            $table->foreign('teacher_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }
};
