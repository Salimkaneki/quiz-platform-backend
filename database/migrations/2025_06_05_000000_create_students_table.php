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
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('student_number')->unique(); // Numéro étudiant
            $table->string('first_name'); 
            $table->string('last_name');
            $table->date('birth_date'); 
            $table->string('email')->unique();
            $table->string('phone')->nullable(); 
            $table->foreignId('class_id')->constrained('classes')
                ->onDelete('cascade'); 
            $table->boolean('is_active')->default(true); // Ajout du champ is_active manquant
            $table->json('metadata')->nullable(); // Métadonnées supplémentaires (JSON)
            
            $table->timestamps();

            // Correction de la syntaxe : ajout du '>' manquant
            $table->index(['class_id', 'is_active']);
            $table->index('student_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};