<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InstitutionController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


// Route::apiResource('institutions', InstitutionController::class);

    Route::get('institutions', [InstitutionController::class, 'index']);
    Route::post('institutions', [InstitutionController::class, 'store']);
    Route::get('institutions/{id}', [InstitutionController::class, 'show']);
    Route::put('institutions/{id}', [InstitutionController::class, 'update']);
    Route::delete('institutions/{id}', [InstitutionController::class, 'destroy']);

