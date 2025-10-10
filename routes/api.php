<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


// =================== IMPORTS ===================
use App\Http\Controllers\Management\UserController;
use App\Http\Controllers\Management\InstitutionController;

// Admin Controllers
use App\Http\Controllers\Management\AdministratorController;
use App\Http\Controllers\Admin\TeacherController;
use App\Http\Controllers\Auth\AdminAuthController;
use App\Http\Controllers\Management\FormationController;
use App\Http\Controllers\Management\SubjectController;
use App\Http\Controllers\Management\ClasseController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Management\TeacherSubjectController;
use App\Http\Controllers\Admin\StudentImportController;
use App\Http\Controllers\Admin\QuizController as AdminQuizController;
use App\Http\Controllers\Admin\AdminQuizSessionController;

// Teacher Controllers
use App\Http\Controllers\Auth\TeacherAuthController;
use App\Http\Controllers\Quiz\QuizSessionController;
use App\Http\Controllers\Quiz\QuizController;
use App\Http\Controllers\Quiz\QuestionController;
use App\Http\Controllers\Teacher\TeacherNotificationController;

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
Route::prefix('admin')->name('admin.')->middleware(['auth:sanctum', 'admin'])->group(function () {
            
        // ===== TEACHERS ADMIN =====
        Route::prefix('teachers')->name('teachers.')->group(function () {
            Route::get('/', [TeacherController::class, 'index'])->name('index');
            Route::post('/', [TeacherController::class, 'store'])->name('store');
            
            // Routes statiques AVANT les routes paramétrées
            Route::get('/users', [TeacherController::class, 'availableUsers'])->name('users');
            Route::get('/with-subjects', [TeacherSubjectController::class, 'teachersWithSubjects'])->name('with-subjects');
            
            // Routes paramétrées APRÈS les routes statiques
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
// ===== STUDENTS =====
    Route::prefix('students')->name('students.')->group(function () {
        Route::get('/', [StudentController::class, 'index'])->name('index');
        Route::post('/', [StudentController::class, 'store'])->name('store');
        Route::get('/{student}', [StudentController::class, 'show'])->name('show');
        Route::put('/{student}', [StudentController::class, 'update'])->name('update');
        Route::delete('/{student}', [StudentController::class, 'destroy'])->name('destroy');
        
        // Import spécifique
        Route::post('import', [StudentImportController::class, 'import'])->name('import');
        
        // ===== NOUVELLES ROUTES AJOUTÉES =====
        Route::get('by-class/{classId}', [StudentController::class, 'getByClass'])->name('by_class');
        // Route::get('by-class', [StudentController::class, 'getByClass'])->name('by_class_query');
        Route::get('by-formation/{formationId}', [StudentController::class, 'getByFormation'])->name('by_formation');
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

    // ===== QUIZ SESSIONS (Gestion admin) =====
    Route::prefix('quiz-sessions')->name('quiz_sessions.')->group(function () {
        Route::get('/', [AdminQuizSessionController::class, 'index'])->name('index');
        Route::post('/', [AdminQuizSessionController::class, 'store'])->name('store');
        Route::get('/available-quizzes', [AdminQuizSessionController::class, 'getAvailableQuizzes'])->name('available_quizzes');
        Route::get('/available-teachers', [AdminQuizSessionController::class, 'getAvailableTeachers'])->name('available_teachers');
        Route::get('/statistics', [AdminQuizSessionController::class, 'getStatistics'])->name('statistics');
        
        Route::get('/{id}', [AdminQuizSessionController::class, 'show'])->name('show');
        Route::put('/{id}', [AdminQuizSessionController::class, 'update'])->name('update');
        Route::delete('/{id}', [AdminQuizSessionController::class, 'destroy'])->name('destroy');
        
        // Actions sur les sessions
        Route::patch('/{id}/activate', [AdminQuizSessionController::class, 'activate'])->name('activate');
        Route::patch('/{id}/complete', [AdminQuizSessionController::class, 'complete'])->name('complete');
        Route::patch('/{id}/cancel', [AdminQuizSessionController::class, 'cancel'])->name('cancel');
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
        Route::get('my-subjects', [TeacherAuthController::class, 'mySubjects'])->name('my_subjects');
        Route::post('logout', [TeacherAuthController::class, 'logout'])->name('logout');
    });
});

// ===== TEACHER RESOURCES (Toutes protégées) =====
Route::prefix('teacher')->name('teacher.')->middleware(['auth:sanctum', 'teacher'])->group(function () {
    
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
        Route::delete('/{id}', [QuizSessionController::class, 'destroy'])->name('destroy');
        
        // Actions sur les sessions
        Route::patch('/{id}/activate', [QuizSessionController::class, 'activate'])->name('activate');
        Route::patch('/{id}/complete', [QuizSessionController::class, 'complete'])->name('complete');
        Route::patch('/{id}/cancel', [QuizSessionController::class, 'cancel'])->name('cancel');
        
        // Gestion des doublons
        Route::get('duplicates', [QuizSessionController::class, 'detectDuplicates'])->name('duplicates.detect');
        Route::post('clean-duplicates', [QuizSessionController::class, 'cleanDuplicates'])->name('duplicates.clean');
    });
    
    // ===== NOTIFICATIONS ENSEIGNANT =====
    Route::prefix('notifications')->name('teacher.notifications.')->group(function () {
        Route::get('/', [TeacherNotificationController::class, 'index'])->name('index');
        Route::get('/unread-count', [TeacherNotificationController::class, 'getUnreadCount'])->name('unread_count');
        Route::patch('/{id}/read', [TeacherNotificationController::class, 'markAsRead'])->name('mark_read');
        Route::patch('/bulk-read', [TeacherNotificationController::class, 'markBulkAsRead'])->name('bulk_read');
        Route::patch('/all-read', [TeacherNotificationController::class, 'markAllAsRead'])->name('all_read');
        Route::delete('/{id}', [TeacherNotificationController::class, 'destroy'])->name('destroy');
    });
});

use App\Http\Controllers\Auth\StudentAuthController;

Route::prefix('student/auth')->group(function () {
    Route::post('login', [StudentAuthController::class, 'login']);
    
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [StudentAuthController::class, 'logout']);
        Route::get('me', [StudentAuthController::class, 'me']);
        Route::get('me', [StudentAuthController::class, 'me']);
    });
});

use App\Http\Controllers\Student\StudentSessionController;

// Route protégée par Sanctum (auth:sanctum)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/student/sessions', [StudentSessionController::class, 'index']);
    Route::get('/student/sessions/{id}', [StudentSessionController::class, 'show']);
    Route::post('/student/session/join', [StudentSessionController::class, 'joinSession']);
    
    // Nouvelles routes pour la navigation dans le quiz
    Route::get('/student/session/{sessionId}/questions', [StudentSessionController::class, 'getQuestions']);
    Route::get('/student/session/{sessionId}/questions/{questionId}', [StudentSessionController::class, 'getQuestion']);
    Route::get('/student/session/{sessionId}/progress', [StudentSessionController::class, 'getProgress']);
});


use App\Http\Controllers\Student\StudentNotificationController;

// Groupe de routes pour étudiants avec middleware auth
Route::middleware(['auth:sanctum', 'student'])->prefix('student')->group(function () {

    // ===== PROFIL ÉTUDIANT =====
    Route::prefix('profile')->group(function () {
        Route::get('/', [\App\Http\Controllers\Student\StudentProfileController::class, 'show'])->name('student.profile.show');
        Route::put('/', [\App\Http\Controllers\Student\StudentProfileController::class, 'update'])->name('student.profile.update');
        Route::post('/change-password', [\App\Http\Controllers\Student\StudentProfileController::class, 'changePassword'])->name('student.profile.change_password');
        Route::post('/picture', [\App\Http\Controllers\Student\StudentProfileController::class, 'uploadProfilePicture'])->name('student.profile.upload_picture');
        Route::delete('/picture', [\App\Http\Controllers\Student\StudentProfileController::class, 'deleteProfilePicture'])->name('student.profile.delete_picture');
    });

    // ===== TABLEAU DE BORD ÉTUDIANT =====
    Route::get('/dashboard', [\App\Http\Controllers\Student\StudentDashboardController::class, 'index'])->name('student.dashboard');

    // ===== NOTIFICATIONS ÉTUDIANT =====
    Route::prefix('notifications')->name('student.notifications.')->group(function () {
        Route::get('/', [StudentNotificationController::class, 'index'])->name('index');
        Route::get('/unread-count', [StudentNotificationController::class, 'getUnreadCount'])->name('unread_count');
        Route::patch('/{id}/read', [StudentNotificationController::class, 'markAsRead'])->name('mark_read');
        Route::patch('/bulk-read', [StudentNotificationController::class, 'markBulkAsRead'])->name('bulk_read');
        Route::patch('/all-read', [StudentNotificationController::class, 'markAllAsRead'])->name('all_read');
        Route::delete('/{id}', [StudentNotificationController::class, 'destroy'])->name('destroy');
    });

    // Soumettre les réponses d'une session (Result)
    Route::post('/results/{resultId}/responses', [\App\Http\Controllers\Student\StudentResponseController::class, 'submitResponses'])
         ->name('student.responses.submit');

    // Optionnel : récupérer toutes les réponses d'un résultat (lecture)
    Route::get('/results/{resultId}/responses', [\App\Http\Controllers\Student\StudentResponseController::class, 'index'])
         ->name('student.responses.index');

    // Optionnel : récupérer une réponse spécifique
    Route::get('/results/{resultId}/responses/{questionId}', [\App\Http\Controllers\Student\StudentResponseController::class, 'show'])
         ->name('student.responses.show');
});

use App\Http\Controllers\Quiz\ResultController;

Route::middleware(['auth:sanctum', 'teacher'])->prefix('teacher')->group(function () {
    
    Route::get('quiz-sessions/{quizSessionId}/results', [ResultController::class, 'index']);
    Route::get('results/{id}', [ResultController::class, 'show']);
    Route::put('results/{id}', [ResultController::class, 'update']);
    Route::put('results/{resultId}/responses/{responseId}', [ResultController::class, 'updateResponse']);
    Route::post('results/{id}/mark-graded', [ResultController::class, 'markAsGraded']);
    Route::post('results/{id}/publish', [ResultController::class, 'publish']);
    Route::get('quiz/{quizId}/results', [ResultController::class, 'allResultsForQuiz']); 
});


// routes/api.php
// Route::get('teacher/quiz-sessions/{quizSessionId}/results', [ResultController::class, 'allResultsForQuiz']);


// =================== ROUTES FALLBACK ===================

// Route de fallback pour les API
Route::fallback(function(){
    return response()->json([
        'message' => 'Route not found.'
    ], 404);
});
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\AdminTeacherNotificationController;
use App\Http\Controllers\Admin\NotificationController;

Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    // Dashboard
    Route::get('/admin/dashboard', [DashboardController::class, 'index']);
    Route::get('/admin/dashboard/charts/{chartType}', [DashboardController::class, 'chartData']);
    
    // Reports - Rapports
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/sessions', [ReportController::class, 'getAvailableSessions'])->name('sessions');
        Route::post('/sessions/{sessionId}/send', [ReportController::class, 'sendSessionReport'])->name('session.send');
        Route::post('/periodic', [ReportController::class, 'sendPeriodicReport'])->name('periodic.send');
    });

    // Notifications de plateforme
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::get('/unread-count', [NotificationController::class, 'getUnreadCount'])->name('unread_count');
        Route::patch('/{id}/read', [NotificationController::class, 'markAsRead'])->name('mark_read');
        Route::patch('/bulk-read', [NotificationController::class, 'markBulkAsRead'])->name('bulk_read');
        Route::patch('/all-read', [NotificationController::class, 'markAllAsRead'])->name('all_read');
        Route::delete('/{id}', [NotificationController::class, 'destroy'])->name('destroy');
        Route::post('/cleanup', [NotificationController::class, 'cleanupExpired'])->name('cleanup');
    });

    // Notifications vers les enseignants
    Route::prefix('admin/teacher-notifications')->name('admin.teacher_notifications.')->group(function () {
        Route::get('/teachers', [AdminTeacherNotificationController::class, 'getAvailableTeachers'])->name('teachers');
        Route::post('/send-to-all', [AdminTeacherNotificationController::class, 'sendToAllTeachers'])->name('send_to_all');
        Route::post('/send-to-teacher/{teacherId}', [AdminTeacherNotificationController::class, 'sendToSpecificTeacher'])->name('send_to_teacher');
        Route::post('/send-to-multiple', [AdminTeacherNotificationController::class, 'sendToMultipleTeachers'])->name('send_to_multiple');
    });
});