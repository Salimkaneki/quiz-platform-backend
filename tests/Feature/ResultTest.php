<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Teacher;
use App\Models\Administrator;
use App\Models\QuizSession;
use App\Models\Result;
use App\Models\Student;
use App\Models\StudentResponse;
use Laravel\Sanctum\Sanctum;

class ResultTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function teacher_can_retrieve_results_from_own_session()
    {
        // Créer un utilisateur enseignant
        $teacherUser = User::factory()->create(['account_type' => 'teacher']);
        $teacher = Teacher::factory()->create(['user_id' => $teacherUser->id]);

        // Créer une session de quiz pour cet enseignant
        $quizSession = QuizSession::factory()->create([
            'teacher_id' => $teacher->user_id,
            'status' => 'completed'
        ]);

        // Créer des résultats pour cette session
        $student = Student::factory()->create();
        $result = Result::factory()->create([
            'quiz_session_id' => $quizSession->id,
            'student_id' => $student->id,
            'status' => 'published'
        ]);

        // Authentifier l'enseignant
        Sanctum::actingAs($teacherUser);

        // Tester la récupération des résultats
        $response = $this->getJson("/api/teacher/sessions/{$quizSession->id}/results");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    '*' => [
                        'id',
                        'student_id',
                        'quiz_session_id',
                        'percentage',
                        'grade',
                        'student' => [
                            'id',
                            'first_name',
                            'last_name'
                        ]
                    ]
                ]);
    }

    /** @test */
    public function teacher_cannot_retrieve_results_from_other_teacher_session()
    {
        // Créer deux enseignants
        $teacherUser1 = User::factory()->create(['account_type' => 'teacher']);
        $teacher1 = Teacher::factory()->create(['user_id' => $teacherUser1->id]);

        $teacherUser2 = User::factory()->create(['account_type' => 'teacher']);
        $teacher2 = Teacher::factory()->create(['user_id' => $teacherUser2->id]);

        // Créer une session pour le deuxième enseignant
        $quizSession = QuizSession::factory()->create([
            'teacher_id' => $teacher2->user_id,
            'status' => 'completed'
        ]);

        // Créer des résultats
        $student = Student::factory()->create();
        Result::factory()->create([
            'quiz_session_id' => $quizSession->id,
            'student_id' => $student->id
        ]);

        // Authentifier le premier enseignant
        Sanctum::actingAs($teacherUser1);

        // Tester que l'accès est refusé
        $response = $this->getJson("/api/teacher/sessions/{$quizSession->id}/results");

        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_retrieve_published_results_from_institution_session()
    {
        // Créer un administrateur pédagogique
        $adminUser = User::factory()->create(['account_type' => 'admin']);
        $admin = Administrator::factory()->create([
            'user_id' => $adminUser->id,
            'type' => 'pedagogique'
        ]);

        // Créer un enseignant dans la même institution
        $teacher = Teacher::factory()->create(['institution_id' => $admin->institution_id]);

        // Créer une session terminée
        $quizSession = QuizSession::factory()->create([
            'teacher_id' => $teacher->user_id,
            'status' => 'completed'
        ]);

        // Créer des résultats publiés
        $student = Student::factory()->create();
        $result = Result::factory()->create([
            'quiz_session_id' => $quizSession->id,
            'student_id' => $student->id,
            'status' => 'published'
        ]);

        // Authentifier l'admin
        Sanctum::actingAs($adminUser);

        // Tester la récupération
        $response = $this->getJson("/api/admin/quiz-sessions/{$quizSession->id}/results");

        $response->assertStatus(200)
                ->assertJsonCount(1)
                ->assertJsonStructure([
                    '*' => [
                        'id',
                        'student_id',
                        'quiz_session_id',
                        'percentage',
                        'grade',
                        'student' => [
                            'id',
                            'first_name',
                            'last_name'
                        ]
                    ]
                ]);
    }

    /** @test */
    public function admin_cannot_retrieve_unpublished_results()
    {
        // Créer un admin
        $adminUser = User::factory()->create(['account_type' => 'admin']);
        $admin = Administrator::factory()->create([
            'user_id' => $adminUser->id,
            'type' => 'pedagogique'
        ]);

        // Créer un enseignant dans la même institution
        $teacher = Teacher::factory()->create(['institution_id' => $admin->institution_id]);

        // Créer une session
        $quizSession = QuizSession::factory()->create([
            'teacher_id' => $teacher->user_id,
            'status' => 'completed'
        ]);

        // Créer des résultats NON publiés
        $student = Student::factory()->create();
        Result::factory()->create([
            'quiz_session_id' => $quizSession->id,
            'student_id' => $student->id,
            'status' => 'graded' // Pas published
        ]);

        // Authentifier l'admin
        Sanctum::actingAs($adminUser);

        // Tester que rien n'est retourné
        $response = $this->getJson("/api/admin/quiz-sessions/{$quizSession->id}/results");

        $response->assertStatus(200)
                ->assertJsonCount(0);
    }

    /** @test */
    public function teacher_can_publish_result()
    {
        // Créer un enseignant
        $teacherUser = User::factory()->create(['account_type' => 'teacher']);
        $teacher = Teacher::factory()->create(['user_id' => $teacherUser->id]);

        // Créer une session
        $quizSession = QuizSession::factory()->create([
            'teacher_id' => $teacher->user_id,
            'status' => 'completed'
        ]);

        // Créer un résultat
        $result = Result::factory()->create([
            'quiz_session_id' => $quizSession->id,
            'status' => 'graded'
        ]);

        // Authentifier l'enseignant
        Sanctum::actingAs($teacherUser);

        // Publier le résultat
        $response = $this->postJson("/api/teacher/results/{$result->id}/publish");

        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'Résultat publié',
                    'result' => [
                        'id' => $result->id,
                        'status' => 'published'
                    ]
                ]);

        // Vérifier en base
        $this->assertDatabaseHas('results', [
            'id' => $result->id,
            'status' => 'published'
        ]);
    }
}