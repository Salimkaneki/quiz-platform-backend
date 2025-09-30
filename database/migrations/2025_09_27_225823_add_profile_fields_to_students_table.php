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
        Schema::table('students', function (Blueprint $table) {
            $table->date('date_of_birth')->nullable()->after('birth_date');
            $table->text('address')->nullable()->after('phone');
            $table->string('emergency_contact')->nullable()->after('address');
            $table->string('emergency_phone', 20)->nullable()->after('emergency_contact');
            $table->text('medical_info')->nullable()->after('emergency_phone');
            $table->json('preferences')->nullable()->after('medical_info');
            $table->string('profile_picture')->nullable()->after('preferences');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn([
                'date_of_birth',
                'address',
                'emergency_contact',
                'emergency_phone',
                'medical_info',
                'preferences',
                'profile_picture'
            ]);
        });
    }
};
