<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\StatusController;
use App\Http\Controllers\Api\TasksController;
use App\Http\Controllers\Api\UsersController;


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

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// Route::get('/tasks', [TasksController::class, 'index']);
Route::get('/statuses', [StatusController::class, 'index']);


Route::group(['middleware' => ['auth:sanctum']], function() {

    // routes that require authentication
    // Route::resource('/users', [UsersController::class, 'index']);

    //Tasks
    Route::get('/tasks', [TasksController::class, 'index']);
    Route::post('/my-tasks', [TasksController::class, 'my_tasks']);

    Route::post('/tasks', [TasksController::class, 'store']);
    Route::post('/tasks/{id}', [TasksController::class, 'update'])->where('id', '[0-9]+');;
    Route::delete('/tasks', [TasksController::class, 'destroy']);


    //Statuses
    Route::get('/statuses', [StatusController::class, 'index']);
    Route::get('/status/tasks', [StatusController::class, 'getTaskStatus']);
    Route::get('/status/users', [StatusController::class, 'getTaskUsers']);
    Route::get('/status/stats', [StatusController::class, 'getTaskStats']);


    //Users
    Route::post('/task/update', [TasksController::class, 'update_tasks']);



    // Route::resource('/status', [StatusController::class, 'index']);
    Route::post('/logout', 'AuthController@logout');
});


    
