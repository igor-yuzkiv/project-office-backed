<?php

use App\Http\Controllers\Attachments\AttachmentsController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Comment\CommentController;
use App\Http\Controllers\Projects\ProjectAttachmentsController;
use App\Http\Controllers\Projects\ProjectsController;
use App\Http\Controllers\Tags\TagsController;
use App\Http\Controllers\TaskLists\TaskListsController;
use App\Http\Controllers\Tasks\TaskAttachmentsController;
use App\Http\Controllers\Tasks\TaskCommentsController;
use App\Http\Controllers\Tasks\TaskOwnersController;
use App\Http\Controllers\Tasks\TasksController;
use App\Http\Controllers\Users\UsersController;
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
 * Project Attachments
 */
Route::group([
    'prefix'     => 'projects/{project}/attachments',
    'as'         => 'projects.attachments.',
    'middleware' => ['auth:sanctum'],
    'controller' => ProjectAttachmentsController::class,
], function () {
    Route::post('/', 'store')->name('store');
});

/**
 * Task Comments
 */
Route::group([
    'prefix'     => 'tasks/{task}/comments',
    'as'         => 'tasks.comments.',
    'middleware' => ['auth:sanctum'],
    'controller' => TaskCommentsController::class,
], function () {
    Route::get('/', 'index')->name('index');
    Route::post('/', 'store')->name('store');
});

/**
 * Task Attachments
 */
Route::group([
    'prefix'     => 'tasks/{task}/attachments',
    'as'         => 'tasks.attachments.',
    'middleware' => ['auth:sanctum'],
    'controller' => TaskAttachmentsController::class,
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
    Route::delete('{attachment}', 'destroy')->name('destroy');
    Route::get('{attachment}/content', 'content')->name('content');
    Route::get('{attachment}/download', 'download')->name('download');
    Route::get('{attachment}/temporary-url', 'temporaryUrl')->name('temporaryUrl');
});

/**
 * Task Owners
 */
Route::group([
    'prefix'     => 'tasks/{task}/owners',
    'as'         => 'tasks.owners.',
    'middleware' => ['auth:sanctum'],
    'controller' => TaskOwnersController::class,
], function () {
    Route::get('/', 'index')->name('index');
    Route::put('/', 'sync')->name('sync');
});

/**
 * Users
 */
Route::get('users', [UsersController::class, 'index'])->middleware(['auth:sanctum'])->name('users.index');

/**
 * Comments
 */
Route::group([
    'prefix'     => 'comments',
    'as'         => 'comments.',
    'middleware' => ['auth:sanctum'],
    'controller' => CommentController::class,
], function () {
    Route::patch('{comment}', 'update')->name('update');
    Route::delete('{comment}', 'destroy')->name('destroy');
});
