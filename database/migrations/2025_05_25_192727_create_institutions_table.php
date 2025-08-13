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
        Schema::create('institutions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('slug')->unique(); // Pour les URLs
            $table->text('description')->nullable();
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable()->unique();
            $table->string('website')->nullable();
            $table->string('timezone')->default('UTC');
            $table->json('settings')->nullable();
            $table->boolean('is_active')->default(true);
            $table->softDeletes(); // Ajout important pour l'archivage
            $table->timestamps();

            $table->index(['code', 'is_active']); // Pour les requêtes fréquentes
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('institutions');
    }
};
