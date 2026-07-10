<?php

use App\Http\CliApi\Controllers\ProjectDocuments\ProjectDocumentsController;
use App\Http\CliApi\Controllers\Projects\ProjectsController;
use App\Http\CliApi\Controllers\Tasks\TaskCommentsController;
use App\Http\CliApi\Controllers\Tasks\TasksController;
use App\Http\CliApi\Controllers\TestController;
use App\Http\CliApi\Controllers\Users\UserController;
use Illuminate\Support\Facades\Route;

Route::get('test', [TestController::class, 'index'])->middleware('auth:sanctum');

Route::get('me', [UserController::class, 'me'])->middleware('auth:sanctum')->name('me');

/**
 * Projects
 */
Route::middleware('auth:sanctum')
    ->prefix('projects/{project}')
    ->name('projects.')
    ->group(function () {
        Route::get('/', [ProjectsController::class, 'show'])->name('show');

        /**
         * Tasks
         */
        Route::prefix('tasks')
            ->name('tasks.')
            ->scopeBindings()
            ->group(function () {
                Route::get('list', [TasksController::class, 'index'])->name('index');
                Route::post('/', [TasksController::class, 'store'])->name('store');
                Route::get('{task}', [TasksController::class, 'show'])->name('show');
                Route::put('{task}', [TasksController::class, 'update'])->name('update');

                /**
                 * Task Comments
                 */
                Route::prefix('{task}/comments')
                    ->name('comments.')
                    ->group(function () {
                        Route::get('/', [TaskCommentsController::class, 'index'])->name('index');
                        Route::post('/', [TaskCommentsController::class, 'store'])->name('store');
                        Route::put('{comment}', [TaskCommentsController::class, 'update'])->name('update');
                    });
            });

        /**
         * Project Documents
         */
        Route::prefix('docs')
            ->name('docs.')
            ->scopeBindings()
            ->group(function () {
                Route::post('/', [ProjectDocumentsController::class, 'store'])->name('store');
                Route::get('{document}', [ProjectDocumentsController::class, 'show'])->name('show');
                Route::put('{document}', [ProjectDocumentsController::class, 'update'])->name('update');
            });
    });
