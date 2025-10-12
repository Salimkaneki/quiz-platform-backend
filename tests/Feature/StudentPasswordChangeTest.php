<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Student;
use App\Models\Institution;
use App\Models\Formation;
use App\Models\Classes;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StudentPasswordChangeTest extends TestCase
{
    use RefreshDatabase;

    private $student;
    private $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Create institution
        $institution = Institution::create([
            'name' => 'École Supérieure de Gestion et d\'Informatique du Togo',
            'code' => 'ESGIS-TOGO',
            'type' => 'university'
        ]);

        // Create formation
        $formation = Formation::create([
            'name' => 'Informatique et Technologies Numériques',
            'code' => 'INFO-ESGIS-TOGO',
            'institution_id' => $institution->id,
        ]);

        // Create class
        $class = Classes::create([
            'name' => 'L1 Informatique ESGIS Togo',
            'level' => 1,
            'academic_year' => '2024-2025',
            'formation_id' => $formation->id,
            'max_students' => 50,
        ]);

        // Create user
        $this->user = User::factory()->create([
            'account_type' => 'student',
            'email' => 'student@test.com',
            'password' => Hash::make('oldpassword123')
        ]);

        // Create associated student record
        $this->student = Student::create([
            'user_id' => $this->user->id,
            'student_number' => 'STU001',
            'first_name' => 'Test',
            'last_name' => 'Student',
            'birth_date' => '2000-01-01',
            'email' => 'student@test.com',
            'class_id' => $class->id,
        ]);
    }

    /**
     * Test successful password change
     */
    public function test_successful_password_change(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
                        ->postJson('/api/student/profile/change-password', [
                            'current_password' => 'oldpassword123',
                            'password' => 'NewPassword123',
                            'password_confirmation' => 'NewPassword123'
                        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'Mot de passe changé avec succès'
                ]);

        // Verify password was actually changed
        $this->user->refresh();
        $this->assertTrue(Hash::check('NewPassword123', $this->user->password));
    }

    /**
     * Test password change with wrong current password
     */
    public function test_password_change_with_wrong_current_password(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
                        ->postJson('/api/student/profile/change-password', [
                            'current_password' => 'wrongpassword',
                            'password' => 'NewPassword123',
                            'password_confirmation' => 'NewPassword123'
                        ]);

        $response->assertStatus(400)
                ->assertJson([
                    'error' => 'Le mot de passe actuel est incorrect'
                ]);
    }

    /**
     * Test password change with same password
     */
    public function test_password_change_with_same_password(): void
    {
        // First change to a different password, then try to change back to the same
        $this->actingAs($this->user, 'sanctum')
             ->postJson('/api/student/profile/change-password', [
                 'current_password' => 'oldpassword123',
                 'password' => 'TempPassword123',
                 'password_confirmation' => 'TempPassword123'
             ]);

        // Now try to change back to the same password (which should fail)
        $response = $this->actingAs($this->user, 'sanctum')
                        ->postJson('/api/student/profile/change-password', [
                            'current_password' => 'TempPassword123',
                            'password' => 'TempPassword123',
                            'password_confirmation' => 'TempPassword123'
                        ]);

        $response->assertStatus(400)
                ->assertJson([
                    'error' => 'Le nouveau mot de passe doit être différent de l\'actuel'
                ]);
    }

    /**
     * Test password change with non-matching confirmation
     */
    public function test_password_change_with_non_matching_confirmation(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
                        ->postJson('/api/student/profile/change-password', [
                            'current_password' => 'oldpassword123',
                            'password' => 'NewPassword123',
                            'password_confirmation' => 'DifferentPassword123'
                        ]);

        $response->assertStatus(422); // Validation error
    }

    /**
     * Test password change with weak password
     */
    public function test_password_change_with_weak_password(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
                        ->postJson('/api/student/profile/change-password', [
                            'current_password' => 'oldpassword123',
                            'password' => 'weak',
                            'password_confirmation' => 'weak'
                        ]);

        $response->assertStatus(422); // Validation error
    }

    /**
     * Test password change without authentication
     */
    public function test_password_change_without_authentication(): void
    {
        $response = $this->postJson('/api/student/profile/change-password', [
            'current_password' => 'oldpassword123',
            'password' => 'NewPassword123',
            'password_confirmation' => 'NewPassword123'
        ]);

        $response->assertStatus(401);
    }

    /**
     * Test password change by non-student user
     */
    public function test_password_change_by_non_student(): void
    {
        $teacher = User::factory()->create([
            'account_type' => 'teacher',
            'email' => 'teacher@test.com'
        ]);

        $response = $this->actingAs($teacher, 'sanctum')
                        ->postJson('/api/student/profile/change-password', [
                            'current_password' => 'oldpassword123',
                            'password' => 'NewPassword123',
                            'password_confirmation' => 'NewPassword123'
                        ]);

        $response->assertStatus(403);
    }
}