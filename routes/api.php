<?php

use App\Http\Controllers\Attachments\AttachmentsController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Projects\ProjectsController;
use App\Http\Controllers\TaskLists\TaskListsController;
use App\Http\Controllers\Tasks\TasksController;
use Illuminate\Support\Facades\Route;

Route::group([
    'as'         => 'auth.',
    'controller' => AuthController::class,
], function () {
    Route::get('/user', 'user')->name('user')->middleware('auth:sanctum');
    Route::post('/login', 'login')->name('login');
    Route::post('/logout', 'logout')->name('logout')->middleware('auth:sanctum');
});

Route::post('projects/search', [ProjectsController::class, 'search'])->middleware(['auth:sanctum'])->name('projects.search');
Route::apiResource('projects', ProjectsController::class)->middleware(['auth:sanctum']);

Route::apiResource('projects.task-lists', TaskListsController::class)->middleware(['auth:sanctum']);

Route::apiResource('projects.tasks', TasksController::class)->middleware(['auth:sanctum']);

Route::post('attachments', [AttachmentsController::class, 'store'])->middleware(['auth:sanctum'])->name('attachments.store');
