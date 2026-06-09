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

Route::post('task-lists/search', [TaskListsController::class, 'search'])->middleware(['auth:sanctum'])->name('task-lists.search');
Route::apiResource('task-lists', TaskListsController::class)->middleware(['auth:sanctum']);

Route::post('tasks/search', [TasksController::class, 'search'])->middleware(['auth:sanctum'])->name('tasks.search');
Route::apiResource('tasks', TasksController::class)->middleware(['auth:sanctum']);

Route::post('attachments/search', [AttachmentsController::class, 'search'])->middleware(['auth:sanctum'])->name('attachments.search');
Route::post('attachments', [AttachmentsController::class, 'store'])->middleware(['auth:sanctum'])->name('attachments.store');
Route::get('attachments/{attachment}/content', [AttachmentsController::class, 'content'])->middleware(['signed'])->name('attachments.content');
