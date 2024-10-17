<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\AttachmentController;

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
    Route::put('update_status/{task_id}', [TaskController::class , 'update_status']);
    Route::put('task_assign/{task_id}' , [TaskController::class , 'assign']);
    Route::put('task_reassign/{task_id}' , [TaskController::class , 'update_reassign']);
    Route::get('task_blocked' , [TaskController::class , 'task_blocked']);
    Route::get('all_trashed_tasks', [TaskController::class, 'all_trashed_tasks']);
    Route::get('restore_task/{task_id}', [TaskController::class, 'restore']);
    Route::delete('forceDelete_task/{task_id}', [TaskController::class, 'forceDelete']);


    Route::post('add_comment/{id}', [CommentController::class,'store']); 
    Route::put('update_comment/{comment_id}', [CommentController::class,'update']); 
    Route::get('all_comment', [CommentController::class,'index']); 
    Route::delete('delete_comment/{comment_id}', [CommentController::class,'destroy']); 


    Route::post('add_Attachment/{task_id}', [AttachmentController::class,'store']); 
    Route::get('all_Attachment', [AttachmentController::class,'index']); 

    //Route::apiResource('role',RoleController::class); 

});
