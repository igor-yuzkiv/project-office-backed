<?php

use App\Domains\Attachment\Exceptions\AttachmentStorageFailedException;
use App\Domains\ProjectDocument\Exceptions\ProjectDocumentCyclicParentException;
use App\Domains\ProjectDocument\Exceptions\ProjectDocumentMaxDepthExceededException;
use App\Domains\ProjectDocument\Exceptions\ProjectDocumentParentProjectMismatchException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
        then: function () {
            Route::middleware('api')
                ->prefix('api/cli')
                ->name('api-cli.')
                ->group(base_path('routes/api-cli.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->statefulApi();
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );

        $exceptions->render(function (ProjectDocumentCyclicParentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        });

        $exceptions->render(function (ProjectDocumentParentProjectMismatchException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        });

        $exceptions->render(function (ProjectDocumentMaxDepthExceededException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        });

        // Not 422: the request was fine and there is nothing for the caller to correct. The
        // message is written for a reader, so it goes out as it is.
        $exceptions->render(function (AttachmentStorageFailedException $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        });
    })->create();
