<?php

namespace App\Infrastructure\Providers;

use App\Console\Commands\IgorTestCommand;
use App\Domains\Attachment\Services\AttachmentStorageService;
use App\Domains\Attachment\Services\S3AttachmentStorageService;
use Illuminate\Support\ServiceProvider;
use RuntimeException;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(AttachmentStorageService::class, function () {
            return match (config('filesystems.attachments_storage')) {
                's3'    => new S3AttachmentStorageService,
                default => throw new RuntimeException('Unsupported attachments storage provider.'),
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->commands([
            IgorTestCommand::class,
        ]);
    }
}
