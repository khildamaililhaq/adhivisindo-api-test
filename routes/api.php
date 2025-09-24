<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\LearningModuleController;
use App\Http\Controllers\LecturerController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);

Route::middleware('auth.api')->group(function () {
    Route::apiResource('users', UserController::class);
    Route::apiResource('learning-modules', LearningModuleController::class);
    Route::apiResource('lecturers', LecturerController::class);

    // Additional routes for managing lecturer-learning module relationships
    Route::post('lecturers/{id}/learning-modules', [LecturerController::class, 'attachLearningModules']);
    Route::delete('lecturers/{id}/learning-modules', [LecturerController::class, 'detachLearningModules']);
});