<?php

use App\Http\WebApi\Controllers\Attachments\AttachmentsController;
use App\Http\WebApi\Controllers\AuthController;
use App\Http\WebApi\Controllers\Comment\CommentController;
use App\Http\WebApi\Controllers\ProjectDocuments\ProjectDocumentAttachmentsController;
use App\Http\WebApi\Controllers\ProjectDocuments\ProjectDocumentCommentsController;
use App\Http\WebApi\Controllers\ProjectDocuments\ProjectDocumentsController;
use App\Http\WebApi\Controllers\ProjectDocuments\ProjectDocumentTasksController;
use App\Http\WebApi\Controllers\ProjectDocuments\ProjectDocumentTreeController;
use App\Http\WebApi\Controllers\Projects\ProjectAttachmentsController;
use App\Http\WebApi\Controllers\Projects\ProjectsController;
use App\Http\WebApi\Controllers\Tags\TagsController;
use App\Http\WebApi\Controllers\TaskLists\TaskListsController;
use App\Http\WebApi\Controllers\Tasks\TaskAttachmentsController;
use App\Http\WebApi\Controllers\Tasks\TaskCommentsController;
use App\Http\WebApi\Controllers\Tasks\TaskOwnersController;
use App\Http\WebApi\Controllers\Tasks\TaskProjectDocumentsController;
use App\Http\WebApi\Controllers\Tasks\TasksController;
use App\Http\WebApi\Controllers\Users\ApiTokensController;
use App\Http\WebApi\Controllers\Users\UserAvatarController;
use App\Http\WebApi\Controllers\Users\UsersController;
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
 * Project Documents
 */
Route::group([
    'prefix'     => 'projects/{project}/project-documents',
    'as'         => 'projects.project-documents.',
    'middleware' => ['auth:sanctum'],
    'controller' => ProjectDocumentsController::class,
], function () {
    Route::get('/', 'index')->name('index');
    Route::post('/', 'store')->name('store');
});
Route::post('project-documents/search', [ProjectDocumentsController::class, 'search'])->middleware(['auth:sanctum'])->name('project-documents.search');
Route::apiResource('project-documents', ProjectDocumentsController::class)
    ->only(['show', 'update', 'destroy'])
    ->middleware(['auth:sanctum']);
Route::get('projects/{project}/project-documents/tree', [ProjectDocumentTreeController::class, 'index'])
    ->middleware(['auth:sanctum'])->name('projects.project-documents.tree');

/**
 * Project Document Comments
 */
Route::group([
    'prefix'     => 'project-documents/{project_document}/comments',
    'as'         => 'project-documents.comments.',
    'middleware' => ['auth:sanctum'],
    'controller' => ProjectDocumentCommentsController::class,
], function () {
    Route::get('/', 'index')->name('index');
    Route::post('/', 'store')->name('store');
});

/**
 * Project Document Attachments
 */
Route::group([
    'prefix'     => 'project-documents/{project_document}/attachments',
    'as'         => 'project-documents.attachments.',
    'middleware' => ['auth:sanctum'],
    'controller' => ProjectDocumentAttachmentsController::class,
], function () {
    Route::post('/', 'store')->name('store');
});

/**
 * Project Document Tasks
 */
Route::group([
    'prefix'     => 'project-documents/{project_document}/tasks',
    'as'         => 'project-documents.tasks.',
    'middleware' => ['auth:sanctum'],
    'controller' => ProjectDocumentTasksController::class,
], function () {
    Route::get('/', 'index')->name('index');
    Route::put('/', 'sync')->name('sync');
});

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
 * Task Project Documents
 */
Route::group([
    'prefix'     => 'tasks/{task}/project-documents',
    'as'         => 'tasks.project-documents.',
    'middleware' => ['auth:sanctum'],
    'controller' => TaskProjectDocumentsController::class,
], function () {
    Route::get('/', 'index')->name('index');
    Route::put('/', 'sync')->name('sync');
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
 * User Avatar
 */
Route::post('user/avatar', [UserAvatarController::class, 'store'])->middleware(['auth:sanctum'])->name('user.avatar.store');

/**
 * API Tokens
 */
Route::group([
    'prefix'     => 'api-tokens',
    'as'         => 'api-tokens.',
    'middleware' => ['auth:sanctum'],
    'controller' => ApiTokensController::class,
], function () {
    Route::get('/', 'index')->name('index');
    Route::post('/', 'store')->name('store');
    Route::delete('{token}', 'destroy')->name('destroy');
});

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
