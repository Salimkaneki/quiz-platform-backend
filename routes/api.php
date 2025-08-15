<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InstitutionController;
use App\Http\Controllers\Admin\AdministratorController;
use App\Http\Controllers\Admin\TeacherController;
use App\Http\Controllers\UserController;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

// ===== ROUTES USERS =====
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


    Route::get('institutions', [InstitutionController::class, 'index']);
    Route::post('institutions', [InstitutionController::class, 'store']);
    Route::get('institutions/{id}', [InstitutionController::class, 'show']);
    Route::put('institutions/{id}', [InstitutionController::class, 'update']);
    Route::delete('institutions/{id}', [InstitutionController::class, 'destroy']);


Route::prefix('administrators')->group(function () {
    // CRUD de base
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

// ===== ROUTES TEACHERS =====
Route::prefix('teachers')->group(function () {
    // CRUD de base
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