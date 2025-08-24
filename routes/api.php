<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// =================== IMPORTS ===================
use App\Http\Controllers\UserController;
use App\Http\Controllers\InstitutionController;
use App\Http\Controllers\Admin\AdministratorController;
use App\Http\Controllers\Admin\TeacherController;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\FormationController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Admin\ClasseController;
use App\Http\Controllers\Admin\StudentController;

// =================== ROUTES PUBLIQUES ===================

// ===== INSTITUTIONS (Publiques) =====
Route::prefix('institutions')->group(function () {
    Route::get('/', [InstitutionController::class, 'index']);
    Route::post('/', [InstitutionController::class, 'store']);
    Route::get('/{id}', [InstitutionController::class, 'show']);
    Route::put('/{id}', [InstitutionController::class, 'update']);
    Route::delete('/{id}', [InstitutionController::class, 'destroy']);
});

// ===== USERS (Publiques) =====
Route::prefix('users')->group(function () {
    Route::get('/', [UserController::class, 'index'])->name('users.index');
    Route::post('/', [UserController::class, 'store'])->name('users.store');
    Route::get('/{user}', [UserController::class, 'show'])->name('users.show');
    Route::put('/{user}', [UserController::class, 'update'])->name('users.update');
    Route::patch('/{user}', [UserController::class, 'update'])->name('users.patch');
    Route::delete('/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    
    // Routes spécifiques
    Route::get('/account-type/{accountType}', [UserController::class, 'byAccountType'])->name('users.by_account_type');
});

// ===== ADMINISTRATORS (Publiques) =====
Route::prefix('administrators')->group(function () {
    Route::get('/', [AdministratorController::class, 'index'])->name('administrators.index');
    Route::post('/', [AdministratorController::class, 'store'])->name('administrators.store');
    Route::get('/{administrator}', [AdministratorController::class, 'show'])->name('administrators.show');
    Route::put('/{administrator}', [AdministratorController::class, 'update'])->name('administrators.update');
    Route::patch('/{administrator}', [AdministratorController::class, 'update'])->name('administrators.patch');
    Route::delete('/{administrator}', [AdministratorController::class, 'destroy'])->name('administrators.destroy');
    
    // Routes spécifiques
    Route::get('/institution/{institutionId}', [AdministratorController::class, 'byInstitution'])->name('administrators.by_institution');
    Route::get('/type/{type}', [AdministratorController::class, 'byType'])->name('administrators.by_type');
});

// ===== TEACHERS (Publiques) =====
Route::prefix('teachers')->group(function () {
    Route::get('/', [TeacherController::class, 'index'])->name('teachers.index');
    Route::post('/', [TeacherController::class, 'store'])->name('teachers.store');
    Route::get('/{teacher}', [TeacherController::class, 'show'])->name('teachers.show');
    Route::put('/{teacher}', [TeacherController::class, 'update'])->name('teachers.update');
    Route::patch('/{teacher}', [TeacherController::class, 'update'])->name('teachers.patch');
    Route::delete('/{teacher}', [TeacherController::class, 'destroy'])->name('teachers.destroy');
    
    // Routes spécifiques
    Route::get('/permanent', [TeacherController::class, 'permanent'])->name('teachers.permanent');
    Route::get('/grade/{grade}', [TeacherController::class, 'byGrade'])->name('teachers.by_grade');
    Route::get('/my-institution', [TeacherController::class, 'myInstitutionTeachers'])->name('teachers.my_institution');
});

// =================== ROUTES ADMIN ===================

// ===== ADMIN AUTH (Routes spéciales) =====
Route::prefix('admin')->group(function () {
    // Authentification (sans middleware)
    Route::post('login', [AdminAuthController::class, 'login']);

    // Routes protégées par auth
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AdminAuthController::class, 'logout']);
        Route::get('me', [AdminAuthController::class, 'me']);
    });
});

// ===== ADMIN RESOURCES (Toutes protégées) =====
Route::prefix('admin')->middleware('auth:sanctum')->group(function () {
    
    // ===== TEACHERS ADMIN =====
    Route::prefix('teachers')->group(function () {
        Route::get('/', [TeacherController::class, 'index']);
        Route::post('/', [TeacherController::class, 'store']);
        Route::get('/{teacher}', [TeacherController::class, 'show']);
        Route::put('/{teacher}', [TeacherController::class, 'update']);
        Route::delete('/{teacher}', [TeacherController::class, 'destroy']);
    });

    // ===== FORMATIONS =====
    Route::prefix('formations')->group(function () {
        Route::get('/', [FormationController::class, 'index']);        // Liste des formations
        Route::post('/', [FormationController::class, 'store']);       // Création
        Route::get('/{formation}', [FormationController::class, 'show']); // Détails
        Route::put('/{formation}', [FormationController::class, 'update']); // Mise à jour
        Route::delete('/{formation}', [FormationController::class, 'destroy']); // Suppression
    });

    // ===== SUBJECTS (MATIÈRES) =====
    Route::prefix('subjects')->group(function () {
        Route::get('/', [SubjectController::class, 'index']);      // Liste + filtres
        Route::post('/', [SubjectController::class, 'store']);     // Création
        Route::get('/{subject}', [SubjectController::class, 'show']);  // Détail
        Route::put('/{subject}', [SubjectController::class, 'update']); // Mise à jour
        Route::delete('/{subject}', [SubjectController::class, 'destroy']); // Suppression
    });

    // ===== CLASSES =====
    Route::prefix('classes')->group(function () {
        Route::get('/', [ClasseController::class, 'index']);
        Route::post('/', [ClasseController::class, 'store']);
        Route::get('/{classe}', [ClasseController::class, 'show']);
        Route::put('/{classe}', [ClasseController::class, 'update']);
        Route::patch('/{classe}', [ClasseController::class, 'update']);
        Route::delete('/{classe}', [ClasseController::class, 'destroy']);
    });

    // ===== STUDENTS (ÉTUDIANTS) =====
    Route::prefix('students')->group(function () {
        Route::get('/', [StudentController::class, 'index']);
        Route::post('/', [StudentController::class, 'store']);
        Route::get('/{student}', [StudentController::class, 'show']);
        Route::put('/{student}', [StudentController::class, 'update']);
        Route::delete('/{student}', [StudentController::class, 'destroy']);
    });
});
// ===== TEACHER SUBJECTS (Attributions) =====
use App\Http\Controllers\Admin\TeacherSubjectController;

Route::prefix('admin')->middleware('auth:sanctum')->group(function () {
    // Attributions enseignant-matière
    Route::get('teacher-subjects', [TeacherSubjectController::class, 'index']);
    Route::post('teacher-subjects', [TeacherSubjectController::class, 'store']);
    Route::get('teacher-subjects/{teacherSubject}', [TeacherSubjectController::class, 'show']);
    Route::put('teacher-subjects/{teacherSubject}', [TeacherSubjectController::class, 'update']);
    Route::patch('teacher-subjects/{teacherSubject}', [TeacherSubjectController::class, 'update']);
    Route::delete('teacher-subjects/{teacherSubject}', [TeacherSubjectController::class, 'destroy']);
});

use App\Http\Controllers\Admin\StudentImportController;

Route::prefix('admin')->middleware('auth:sanctum')->group(function () {
    Route::post('students/import', [StudentImportController::class, 'import']);
});

use App\Http\Controllers\Auth\TeacherAuthController;

Route::prefix('teacher')->group(function () {
    Route::post('login', [TeacherAuthController::class, 'login']); // POST obligatoire
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('me', [TeacherAuthController::class, 'me']);
        Route::post('logout', [TeacherAuthController::class, 'logout']);
    });
});

// use App\Http\Controllers\Teacher\Quiz\QuizController;

Route::prefix('teacher')->middleware('auth:sanctum')->group(function () {
    // Routes quizzes
    Route::apiResource('quizzes', QuizController::class);
    
    // Routes questions
    Route::prefix('quizzes/{quizId}')->group(function () {
        Route::get('questions', [QuestionController::class, 'index']);
        Route::get('questions/{questionId}', [QuestionController::class, 'show']);
        Route::post('questions', [QuestionController::class, 'store']);
        Route::post('questions/batch', [QuestionController::class, 'batchStore']);
        Route::put('questions/{questionId}', [QuestionController::class, 'update']);
        Route::delete('questions/{questionId}', [QuestionController::class, 'destroy']);
    });
});
