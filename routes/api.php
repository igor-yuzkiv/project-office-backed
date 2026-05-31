<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Projects\ProjectsController;
use App\Http\Controllers\TaskLists\TaskListsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::group([
    'as'         => 'auth.',
    'controller' => AuthController::class,
], function () {
    Route::post('/login', 'login')->name('login');
});

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('projects', ProjectsController::class);
    Route::apiResource('projects.task-lists', TaskListsController::class);
});
