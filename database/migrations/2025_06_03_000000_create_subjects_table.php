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
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Ex: "Programmation Web"
            $table->string('code')->unique(); // Ex: "PROG-WEB-001"
            $table->text('description')->nullable();
            $table->integer('credits')->default(3); // Crédits ECTS
            $table->integer('coefficient')->default(1);
            $table->enum('type', ['cours', 'td', 'tp', 'projet'])->default('cours');
            $table->foreignId('formation_id')->constrained()->cascadeOnDelete();
            $table->integer('semester')->default(1); // Semestre
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['formation_id', 'semester']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subjects');
    }
};
