<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Teacher;
use App\Models\Institution;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeacherMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that teacher middleware allows teacher users
     */
    public function test_teacher_middleware_allows_teacher_users(): void
    {
        // Create institution first
        $institution = Institution::create([
            'name' => 'Test Institution',
            'code' => 'TEST',
            'type' => 'university'
        ]);

        $user = User::factory()->create([
            'account_type' => 'teacher',
            'email' => 'teacher@test.com'
        ]);

        // Create associated teacher record
        $teacher = Teacher::factory()->create([
            'user_id' => $user->id,
            'institution_id' => $institution->id,
        ]);

        // Create a quiz session for testing
        $quiz = \App\Models\Quiz::factory()->create(['teacher_id' => $teacher->id]);
        $session = \App\Models\QuizSession::factory()->create(['quiz_id' => $quiz->id]);

        $response = $this->actingAs($user, 'sanctum')
                        ->get("/api/teacher/quiz-sessions/{$session->id}/results");

        // Should not return 403 (forbidden)
        $response->assertStatus(200);
    }

    /**
     * Test that teacher middleware blocks non-teacher users
     */
    public function test_teacher_middleware_blocks_non_teacher_users(): void
    {
        $student = User::factory()->create([
            'account_type' => 'student',
            'email' => 'student@test.com'
        ]);

        // Create a quiz session for testing
        $quiz = \App\Models\Quiz::factory()->create();
        $session = \App\Models\QuizSession::factory()->create(['quiz_id' => $quiz->id]);

        $response = $this->actingAs($student, 'sanctum')
                        ->get("/api/teacher/quiz-sessions/{$session->id}/results");

        $response->assertStatus(403);
    }

    /**
     * Test that teacher middleware blocks unauthenticated users
     */
    public function test_teacher_middleware_blocks_unauthenticated_users(): void
    {
        // Create a quiz session for testing
        $quiz = \App\Models\Quiz::factory()->create();
        $session = \App\Models\QuizSession::factory()->create(['quiz_id' => $quiz->id]);

        $response = $this->get("/api/teacher/quiz-sessions/{$session->id}/results");

        $response->assertStatus(401);
    }
}
