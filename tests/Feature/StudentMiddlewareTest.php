<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Student;
use App\Models\Institution;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that student middleware allows student users
     */
    public function test_student_middleware_allows_student_users(): void
    {
        // Create institution first
        $institution = Institution::create([
            'name' => 'Test Institution',
            'code' => 'TEST',
            'type' => 'university'
        ]);

        // Create formation
        $formation = \App\Models\Formation::create([
            'name' => 'Test Formation',
            'code' => 'TF',
            'institution_id' => $institution->id,
        ]);

        // Create class
        $class = \App\Models\Classes::create([
            'name' => 'Test Class',
            'level' => 1,
            'academic_year' => '2024-2025',
            'formation_id' => $formation->id,
            'max_students' => 50,
        ]);

        $user = User::factory()->create([
            'account_type' => 'student',
            'email' => 'student@test.com'
        ]);

        // Create associated student record
        Student::create([
            'user_id' => $user->id,
            'student_number' => 'STU001',
            'first_name' => 'Test',
            'last_name' => 'Student',
            'birth_date' => '2000-01-01',
            'email' => 'student@test.com',
            'class_id' => $class->id,
        ]);

        $response = $this->actingAs($user, 'sanctum')
                        ->get('/api/student/profile');

        // Should not return 403 (forbidden)
        $response->assertStatus(200);
    }

    /**
     * Test that student middleware blocks non-student users
     */
    public function test_student_middleware_blocks_non_student_users(): void
    {
        $teacher = User::factory()->create([
            'account_type' => 'teacher',
            'email' => 'teacher@test.com'
        ]);

        $response = $this->actingAs($teacher, 'sanctum')
                        ->get('/api/student/profile');

        $response->assertStatus(403);
    }

    /**
     * Test that student middleware blocks unauthenticated users
     */
    public function test_student_middleware_blocks_unauthenticated_users(): void
    {
        $response = $this->get('/api/student/profile');

        $response->assertStatus(401);
    }
}
