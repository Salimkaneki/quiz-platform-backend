<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// =================== IMPORTS ===================
use App\Http\Controllers\UserController;
use App\Http\Controllers\InstitutionController;

// Admin Controllers
use App\Http\Controllers\Admin\AdministratorController;
use App\Http\Controllers\Admin\TeacherController;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\FormationController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Admin\ClasseController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\TeacherSubjectController;
use App\Http\Controllers\Admin\StudentImportController;

// Teacher Controllers
use App\Http\Controllers\Auth\TeacherAuthController;
use App\Http\Controllers\Teacher\QuizSessionController;
use App\Http\Controllers\Teacher\Quiz\QuizController;
use App\Http\Controllers\Teacher\Quiz\QuestionController;

// =================== ROUTES PUBLIQUES ===================

// ===== INSTITUTIONS =====
Route::prefix('institutions')->name('institutions.')->group(function () {
    Route::get('/', [InstitutionController::class, 'index'])->name('index');
    Route::post('/', [InstitutionController::class, 'store'])->name('store');
    Route::get('/{id}', [InstitutionController::class, 'show'])->name('show');
    Route::put('/{id}', [InstitutionController::class, 'update'])->name('update');
    Route::delete('/{id}', [InstitutionController::class, 'destroy'])->name('destroy');
});

// ===== USERS =====
Route::prefix('users')->name('users.')->group(function () {
    Route::get('/', [UserController::class, 'index'])->name('index');
    Route::post('/', [UserController::class, 'store'])->name('store');
    Route::get('/{user}', [UserController::class, 'show'])->name('show');
    Route::put('/{user}', [UserController::class, 'update'])->name('update');
    Route::patch('/{user}', [UserController::class, 'update'])->name('patch');
    Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
    
    // Routes spécifiques
    Route::get('/account-type/{accountType}', [UserController::class, 'byAccountType'])->name('by_account_type');
});

// ===== ADMINISTRATORS =====
Route::prefix('administrators')->name('administrators.')->group(function () {
    Route::get('/', [AdministratorController::class, 'index'])->name('index');
    Route::post('/', [AdministratorController::class, 'store'])->name('store');
    Route::get('/{administrator}', [AdministratorController::class, 'show'])->name('show');
    Route::put('/{administrator}', [AdministratorController::class, 'update'])->name('update');
    Route::patch('/{administrator}', [AdministratorController::class, 'update'])->name('patch');
    Route::delete('/{administrator}', [AdministratorController::class, 'destroy'])->name('destroy');
    
    // Routes spécifiques
    Route::get('/institution/{institutionId}', [AdministratorController::class, 'byInstitution'])->name('by_institution');
    Route::get('/type/{type}', [AdministratorController::class, 'byType'])->name('by_type');
});

// ===== TEACHERS (Public) =====
Route::prefix('teachers')->name('teachers.')->group(function () {
    Route::get('/', [TeacherController::class, 'index'])->name('index');
    Route::post('/', [TeacherController::class, 'store'])->name('store');
    Route::get('/{teacher}', [TeacherController::class, 'show'])->name('show');
    Route::put('/{teacher}', [TeacherController::class, 'update'])->name('update');
    Route::patch('/{teacher}', [TeacherController::class, 'update'])->name('patch');
    Route::delete('/{teacher}', [TeacherController::class, 'destroy'])->name('destroy');
    
    // Routes spécifiques
    Route::get('/permanent', [TeacherController::class, 'permanent'])->name('permanent');
    Route::get('/grade/{grade}', [TeacherController::class, 'byGrade'])->name('by_grade');
    Route::get('/my-institution', [TeacherController::class, 'myInstitutionTeachers'])->name('my_institution');
});

// =================== ROUTES ADMIN ===================

// ===== ADMIN AUTH =====
Route::prefix('admin')->name('admin.')->group(function () {
    // Routes d'authentification (sans middleware)
    Route::post('login', [AdminAuthController::class, 'login'])->name('login');

    // Routes protégées
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AdminAuthController::class, 'logout'])->name('logout');
        Route::get('me', [AdminAuthController::class, 'me'])->name('me');
    });
});

// ===== ADMIN RESOURCES (Toutes protégées) =====
Route::prefix('admin')->name('admin.')->middleware('auth:sanctum')->group(function () {
    
    // ===== TEACHERS ADMIN =====
    Route::prefix('teachers')->name('teachers.')->group(function () {
        Route::get('/', [TeacherController::class, 'index'])->name('index');
        Route::post('/', [TeacherController::class, 'store'])->name('store');
        Route::get('/{teacher}', [TeacherController::class, 'show'])->name('show');
        Route::put('/{teacher}', [TeacherController::class, 'update'])->name('update');
        Route::delete('/{teacher}', [TeacherController::class, 'destroy'])->name('destroy');
    });

    // ===== FORMATIONS =====
    Route::prefix('formations')->name('formations.')->group(function () {
        Route::get('/', [FormationController::class, 'index'])->name('index');
        Route::post('/', [FormationController::class, 'store'])->name('store');
        Route::get('/{formation}', [FormationController::class, 'show'])->name('show');
        Route::put('/{formation}', [FormationController::class, 'update'])->name('update');
        Route::delete('/{formation}', [FormationController::class, 'destroy'])->name('destroy');
    });

    // ===== SUBJECTS (MATIÈRES) =====
    Route::prefix('subjects')->name('subjects.')->group(function () {
        Route::get('/', [SubjectController::class, 'index'])->name('index');
        Route::post('/', [SubjectController::class, 'store'])->name('store');
        Route::get('/{subject}', [SubjectController::class, 'show'])->name('show');
        Route::put('/{subject}', [SubjectController::class, 'update'])->name('update');
        Route::delete('/{subject}', [SubjectController::class, 'destroy'])->name('destroy');
    });

    // ===== CLASSES =====
    Route::prefix('classes')->name('classes.')->group(function () {
        Route::get('/', [ClasseController::class, 'index'])->name('index');
        Route::post('/', [ClasseController::class, 'store'])->name('store');
        Route::get('/{classe}', [ClasseController::class, 'show'])->name('show');
        Route::put('/{classe}', [ClasseController::class, 'update'])->name('update');
        Route::patch('/{classe}', [ClasseController::class, 'update'])->name('patch');
        Route::delete('/{classe}', [ClasseController::class, 'destroy'])->name('destroy');
    });

    // ===== STUDENTS =====
    Route::prefix('students')->name('students.')->group(function () {
        Route::get('/', [StudentController::class, 'index'])->name('index');
        Route::post('/', [StudentController::class, 'store'])->name('store');
        Route::get('/{student}', [StudentController::class, 'show'])->name('show');
        Route::put('/{student}', [StudentController::class, 'update'])->name('update');
        Route::delete('/{student}', [StudentController::class, 'destroy'])->name('destroy');
        
        // Import spécifique
        Route::post('import', [StudentImportController::class, 'import'])->name('import');
    });

    // ===== TEACHER-SUBJECTS (Attributions) =====
    Route::prefix('teacher-subjects')->name('teacher_subjects.')->group(function () {
        Route::get('/', [TeacherSubjectController::class, 'index'])->name('index');
        Route::post('/', [TeacherSubjectController::class, 'store'])->name('store');
        Route::get('/{teacherSubject}', [TeacherSubjectController::class, 'show'])->name('show');
        Route::put('/{teacherSubject}', [TeacherSubjectController::class, 'update'])->name('update');
        Route::patch('/{teacherSubject}', [TeacherSubjectController::class, 'update'])->name('patch');
        Route::delete('/{teacherSubject}', [TeacherSubjectController::class, 'destroy'])->name('destroy');
    });
});

// =================== ROUTES TEACHER ===================

// ===== TEACHER AUTH =====
Route::prefix('teacher')->name('teacher.')->group(function () {
    // Routes d'authentification (sans middleware)
    Route::post('login', [TeacherAuthController::class, 'login'])->name('login');
    
    // Routes protégées
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('me', [TeacherAuthController::class, 'me'])->name('me');
        Route::post('logout', [TeacherAuthController::class, 'logout'])->name('logout');
    });
});

// ===== TEACHER RESOURCES (Toutes protégées) =====
Route::prefix('teacher')->name('teacher.')->middleware('auth:sanctum')->group(function () {
    
    // ===== QUIZZES =====
    Route::apiResource('quizzes', QuizController::class)->names([
        'index' => 'quizzes.index',
        'store' => 'quizzes.store',
        'show' => 'quizzes.show',
        'update' => 'quizzes.update',
        'destroy' => 'quizzes.destroy'
    ]);

    // ===== QUIZ SESSIONS =====
    Route::prefix('sessions')->name('sessions.')->group(function () {
        Route::get('/', [QuizSessionController::class, 'index'])->name('index');
        Route::post('/', [QuizSessionController::class, 'store'])->name('store');
        Route::get('/{id}', [QuizSessionController::class, 'show'])->name('show');
        Route::put('/{id}', [QuizSessionController::class, 'update'])->name('update');
        
        // Actions sur les sessions
        Route::patch('/{id}/activate', [QuizSessionController::class, 'activate'])->name('activate');
        Route::patch('/{id}/complete', [QuizSessionController::class, 'complete'])->name('complete');
        Route::patch('/{id}/pause', [QuizSessionController::class, 'pause'])->name('pause');
        Route::patch('/{id}/resume', [QuizSessionController::class, 'resume'])->name('resume');
        Route::patch('/{id}/cancel', [QuizSessionController::class, 'cancel'])->name('cancel');
        
        // Gestion des doublons
        Route::get('duplicates', [QuizSessionController::class, 'detectDuplicates'])->name('duplicates.detect');
        Route::post('clean-duplicates', [QuizSessionController::class, 'cleanDuplicates'])->name('duplicates.clean');
    });
    
    // ===== QUESTIONS (Dans le contexte d'un quiz) =====
    Route::prefix('quizzes/{quizId}')->name('quizzes.')->group(function () {
        Route::prefix('questions')->name('questions.')->group(function () {
            Route::get('/', [QuestionController::class, 'index'])->name('index');
            Route::get('/{questionId}', [QuestionController::class, 'show'])->name('show');
            Route::post('/', [QuestionController::class, 'store'])->name('store');
            Route::post('batch', [QuestionController::class, 'batchStore'])->name('batch_store');
            Route::put('/{questionId}', [QuestionController::class, 'update'])->name('update');
            Route::delete('/{questionId}', [QuestionController::class, 'destroy'])->name('destroy');
        });
    });
});

// =================== ROUTES FALLBACK ===================

// Route de fallback pour les API
Route::fallback(function(){
    return response()->json([
        'message' => 'Route not found.'
    ], 404);
});