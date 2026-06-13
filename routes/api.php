<?php

use App\Http\Controllers\Attachments\AttachmentsController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Projects\ProjectsController;
use App\Http\Controllers\Tags\TagsController;
use App\Http\Controllers\TaskLists\TaskListsController;
use App\Http\Controllers\Tasks\TasksController;
use Illuminate\Support\Facades\Route;

/**
 * Auth
 */
Route::group([
    'as'         => 'auth.',
    'controller' => AuthController::class,
], function () {
    Route::get('/user', 'user')->name('user')->middleware('auth:sanctum');
    Route::post('/login', 'login')->name('login');
    Route::post('/logout', 'logout')->name('logout')->middleware('auth:sanctum');
});

/**
 * Projects
 */
Route::post('projects/search', [ProjectsController::class, 'search'])->middleware(['auth:sanctum'])->name('projects.search');
Route::apiResource('projects', ProjectsController::class)->middleware(['auth:sanctum']);

/**
 * Task Lists
 */
Route::post('task-lists/search', [TaskListsController::class, 'search'])->middleware(['auth:sanctum'])->name('task-lists.search');
Route::apiResource('task-lists', TaskListsController::class)->middleware(['auth:sanctum']);

/**
 * Tasks
 */
Route::post('tasks/search', [TasksController::class, 'search'])->middleware(['auth:sanctum'])->name('tasks.search');
Route::apiResource('tasks', TasksController::class)->middleware(['auth:sanctum']);

/**
 * Tags
 */
Route::group([
    'prefix'     => 'tags',
    'as'         => 'tags.',
    'middleware' => ['auth:sanctum'],
    'controller' => TagsController::class,
], function () {
    Route::get('/', 'index')->name('index');
    Route::post('/', 'store')->name('store');
});

/**
 * Attachments
 */
Route::group([
    'prefix'     => 'attachments',
    'as'         => 'attachments.',
    'middleware' => ['auth:sanctum'],
    'controller' => AttachmentsController::class,
], function () {
    Route::post('search', 'search')->name('search');
    Route::post('/', 'store')->name('store');
    Route::delete('{attachment}', 'destroy')->name('destroy');
    Route::get('{attachment}/content', 'content')->name('content');
});
