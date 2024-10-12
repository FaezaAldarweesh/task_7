<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserController;

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

Route::controller(AuthController::class)->group(function () {
    Route::post('login', 'login');
});

Route::group(['middleware' => ['auth:api']], function () {
    // protected routes go here
    Route::post('logout',[AuthController::class ,'logout']); 
    Route::post('refresh', [AuthController::class ,'refresh']);
    
    Route::apiResource('user',UserController::class); 
    Route::get('all_trashed_user', [UserController::class, 'all_trashed_user']);
    Route::get('restore_user/{user_id}', [UserController::class, 'restore']);
    Route::delete('forceDelete_user/{user_id}', [UserController::class, 'forceDelete']);
    
    Route::apiResource('tasks', TaskController::class); 
    Route::get('all_trashed_tasks', [TaskController::class, 'all_trashed_tasks']);
    Route::get('restore_task/{task_id}', [TaskController::class, 'restore']);
    Route::delete('forceDelete_task/{task_id}', [TaskController::class, 'forceDelete']);
});
